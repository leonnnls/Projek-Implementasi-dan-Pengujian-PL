<?php
$servername = "localhost"; // Sesuaikan jika server berbeda
$username = "root"; // Sesuaikan dengan user MySQL Anda
$password = ""; // Masukkan password jika ada
$dbname = "storage"; // Nama database Anda

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
echo "Koneksi berhasil!";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coffee shop</title>
    <link rel="icon" href="./favicon_io/favicon.ico" sizes="any"><!-- 32×32 -->
    <link rel="icon" href="./favicon_io/icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="./favicon_io/apple-touch-icon.png"><!-- 180×180 -->
    <link rel="manifest" href="./favicon_io/manifest.webmanifest">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
</head>
<body>
    <!--header-->
    <header class="header">

        <div class="logo">
            <img src="images/telkom_logo.png" alt="">
        </div>
        
        <nav class="navbar">
            <a href="#home">home</a>
            <a href="#about">about</a>
            <a href="#menu">menu</a>
            <a href="#products">products</a>
            <a href="#review">review</a>
            <a href="#contact">contact</a>
            <a href="#blogs">blogs</a>
        </nav>
    
        <div class="icons">
            <div class="fas fa-search" id="search-btn"></div>
            <div class="fas fa-shopping-cart" id="cart-btn"></div>
            <div class="fas fa-bars" id="menu-btn"></div>
        </div>

        <div class="search-form">
            <input type="search" id="search-box" placeholder="search here...">
            <label for="search-box" class="fas fa-search"></label>
        </div>
    
   <!-- Cart Section -->
<div class="cart-items-container" id="cart-container" style="font-size: 14px; max-width: 600px; margin: 0 auto; border: 1px solid #ccc; padding: 20px; border-radius: 5px;">
    <div id="cart-items"></div>
    <div id="total-price" style="font-size: 18px; font-weight: bold; text-align: right; margin-top: 20px; border-top: 1px solid #ccc; padding-top: 10px;"></div>
    <a href="#" class="btn" id="checkout-btn" style="display:none; margin-top: 10px; text-align: center; display: block;">Checkout Now</a>

    <!-- Area untuk menampilkan QR code setelah checkout -->
    <div id="qrcode-container" style="text-align: center; margin-top: 20px;"></div>
</div>
<!-- End of Cart Section -->

<script>
    // Fungsi untuk memformat harga ke dalam format "Rp. xx.xxx" (opsional tanpa "Rp.")
    function formatCurrency(amount, withRp = true) {
        let formattedAmount = amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        return withRp ? 'Rp. ' + formattedAmount : formattedAmount;
    }

    // Fungsi untuk mengambil data dari sessionStorage dan menampilkan di cart-container
    function loadCartItems() {
        const cartData = sessionStorage.getItem("cartItems");
        const cartItems = cartData ? JSON.parse(cartData) : [];
        const cartBtn = document.getElementById("cart-btn"); // Elemen untuk ikon cart

        // Update badge count pada ikon cart dengan spasi dan ukuran font lebih kecil
        cartBtn.innerHTML = `&nbsp;<span style="font-size: 12px;">${cartItems.length > 0 ? cartItems.length : ''}</span>`;

        const combinedItems = cartItems.reduce((acc, item) => {
            const existingItem = acc.find(i => i.name === item.name);
            if (existingItem) {
                existingItem.quantity += item.quantity || 1;
            } else {
                acc.push({ ...item, quantity: item.quantity || 1 });
            }
            return acc;
        }, []);

        let totalPrice = 0;
        combinedItems.forEach(item => {
            totalPrice += item.price * item.quantity;
        });

        const cartContainer = document.getElementById("cart-items");
        cartContainer.innerHTML = "";

        if (combinedItems.length > 0) {
            document.getElementById("checkout-btn").style.display = "block";

            const header = document.createElement("div");
            header.className = "cart-item cart-header";
            header.style = "display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 0.5fr; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 8px; margin-bottom: 10px;";
            header.innerHTML = `
                <div>Product</div>
                <div>Quantity</div>
                <div>Price</div>
                <div>Total</div>
                <div>Action</div>
            `;
            cartContainer.appendChild(header);

            combinedItems.forEach((item, index) => {
                const itemElement = document.createElement("div");
                itemElement.className = "cart-item";
                itemElement.style = "display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 0.5fr; padding: 8px 0; border-bottom: 1px solid #eee;";
                itemElement.innerHTML = `
                    <div><strong>${item.name}</strong></div>
                    <div style="text-align: center;">${item.quantity}</div>
                    <div style="text-align: right;">${formatCurrency(item.price, false)}</div>
                    <div style="text-align: right;">${formatCurrency(item.price * item.quantity, false)}</div>
                    <div style="text-align: center;">
                        <button onclick="editItem(${index})" style="padding: 2px 5px; font-size: 12px;">Edit</button>
                        <button onclick="deleteItem(${index})" style="padding: 2px 5px; font-size: 12px;">Delete</button>
                    </div>
                `;
                cartContainer.appendChild(itemElement);
            });

            document.getElementById("total-price").innerHTML = `<p>Total Price: ${formatCurrency(totalPrice)}</p>`;
        } else {
            document.getElementById("checkout-btn").style.display = "none";
            cartContainer.innerHTML = "<p>Your cart is empty</p>";
            document.getElementById("total-price").innerHTML = "";
        }
    }

    // Fungsi untuk mengedit item berdasarkan indeks
    function editItem(index) {
        const cartData = JSON.parse(sessionStorage.getItem("cartItems"));
        const item = cartData[index];
        const newQuantity = prompt(`Edit quantity for ${item.name}:`, item.quantity);

        if (newQuantity !== null && !isNaN(newQuantity) && newQuantity > 0) {
            cartData[index].quantity = parseInt(newQuantity, 10);
            sessionStorage.setItem("cartItems", JSON.stringify(cartData));
            loadCartItems();
        } else if (newQuantity == 0) {
            deleteItem(index);
        } else {
            alert("Invalid quantity entered.");
        }
    }

    // Fungsi untuk menghapus item berdasarkan indeks
    function deleteItem(index) {
        const cartData = JSON.parse(sessionStorage.getItem("cartItems"));
        cartData.splice(index, 1);
        sessionStorage.setItem("cartItems", JSON.stringify(cartData));
        loadCartItems();
    }

    // Pantau perubahan pada sessionStorage
    function monitorSessionStorage() {
        let lastCartData = sessionStorage.getItem("cartItems");

        setInterval(() => {
            const currentCartData = sessionStorage.getItem("cartItems");
            if (currentCartData !== lastCartData) {
                lastCartData = currentCartData;
                loadCartItems();  // Muat ulang keranjang ketika data berubah
            }
        }, 500);  // Cek setiap 500 ms
    }

    // Panggil fungsi untuk memuat cart pertama kali
    document.addEventListener("DOMContentLoaded", () => {
        loadCartItems();
        monitorSessionStorage();
    });

    // Event listener untuk mendeteksi perubahan pada sessionStorage di seluruh jendela
    window.addEventListener("storage", function(event) {
        if (event.key === "cartItems") {
            loadCartItems();
        }
    });

    // Fungsi untuk membuat query SQL berdasarkan data di sessionStorage
function generateSQLQuery() {
    const cartData = JSON.parse(sessionStorage.getItem("cartItems"));
    if (!cartData || cartData.length === 0) return null;

    let id = 1; // Bisa dibuat dinamis jika Anda memiliki logika lain untuk ID
    let names = cartData.map(item => item.name).join(", ");
    let prices = cartData.map(item => formatCurrency(item.price || 0, false)).join(", ");
    let quantities = cartData.map(item => `${item.name} ${(item.quantity || 0)}x`).join(", ");
    let totalPrices = cartData.map(item => formatCurrency((item.price || 0) * (item.quantity || 0), false)).join(", ");
    let totalAmount = cartData.reduce((sum, item) => sum + ((item.price || 0) * (item.quantity || 0)), 0);

    // Buat query SQL
    const sqlQuery = `
        INSERT INTO orders (id, name, price, quantity, total_price, total_amount) VALUES
        (${id}, '${names}', '${prices}', '${quantities}', '${totalPrices}', '${formatCurrency(totalAmount, false)}');
    `;
    return sqlQuery;
}


    // Fungsi untuk membuat QR code
    function generateQRCode() {
        const sqlQuery = generateSQLQuery();
        if (!sqlQuery) {
            alert("Keranjang belanja kosong atau terjadi kesalahan!");
            return;
        }

        // Hapus QR code lama jika ada
        document.getElementById("qrcode-container").innerHTML = "";

        // Buat QR code baru
        new QRCode(document.getElementById("qrcode-container"), {
            text: sqlQuery,
            width: 200,
            height: 200
        });

        alert("QR code berhasil dibuat. Silakan simpan atau scan untuk melanjutkan.");
    }

    // Tambahkan event listener pada tombol Checkout
    document.getElementById("checkout-btn").addEventListener("click", function(event) {
        event.preventDefault();
        generateQRCode();
    });
</script>

<!-- End of Cart Section -->


    </header>
    <!--header-->


    <!--home section-->
    <section class="home" id="home">
        <div class="content">
            <h3>Telkom University Coffee Surabaya</h3>
            <p>Setiap cangkir kopi yang anda nikmati adalah bentuk kontribusi untuk kemajuan dan reputasi Telkom University Surabaya #CoffeeForInnovation</p>
            <a href="#" class="btn">get yours now</a>
        </div>
    </section>
    <!--home section-->


    <!--about section-->
    <section class="about" id="about">
        <h1 class="heading"><span>About</span> us </h1>

        <div class="row">
            <div class="image">
                <img src="./images/about-img.jpeg" alt="">
            </div>
            <div class="content">
                <h3>What makes our coffee so special ?</h3>
                <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ad in explicabo tempora. Quo sequi rerum dignissimos non voluptate perspiciatis nihil, ipsam, iure voluptates dolor facere minima repellendus pariatur deleniti exercitationem!
                </p>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consectetur, dolorum ipsum! Sint, nisi minus ipsa saepe dolor aspernatur id placeat
                </p>
                <a href="" class="btn">learn more</a>
            </div>
        </div>
    </section>
    <!--about section-->


<!-- Menu Section -->
<section class="menu" id="menu">
    <h1 class="heading"> our <span>menu</span> </h1>
    <div class="box-container">
        <?php
        // Koneksi ke database telkomcoffee
        $conn = new mysqli('localhost', 'root', '', 'telkomcoffee');

        // Periksa koneksi
        if ($conn->connect_error) {
            die("Koneksi gagal: " . $conn->connect_error);
        }

        // Query untuk mengambil data dari tabel menu_items
        $sql = "SELECT * FROM menu_items";
        $result = $conn->query($sql);

        // Tampilkan data menu
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="box">';
                echo '<img src="' . $row["image_url"] . '" alt="Menu Image">';
                echo '<h3>' . $row["name"] . '</h3>';
                echo '<div class="price">IDR ' . number_format($row["price"], 0, ',', '.') . '</div>';
                // Tambahkan data-attributes untuk menyimpan informasi item
                echo '<button class="btn add-to-cart-btn" data-name="' . $row["name"] . '" data-price="' . $row["price"] . '" data-image="' . $row["image_url"] . '">add to cart</button>';
                echo '</div>';
            }
        } else {
            echo "Tidak ada data menu.";
        }

        // Tutup koneksi
        $conn->close();
        ?>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Ambil semua tombol add-to-cart
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

    // Fungsi untuk menambah item ke cart
    function addToCart(name, price, image) {
        // Ambil data cart dari sessionStorage atau inisialisasi array baru
        let cartItems = JSON.parse(sessionStorage.getItem('cartItems')) || [];

        // Tambahkan item baru ke cartItems
        cartItems.push({ name, price, image });

        // Simpan kembali ke sessionStorage
        sessionStorage.setItem('cartItems', JSON.stringify(cartItems));

        console.log(`Item ditambahkan ke keranjang: ${name}`);
    }

    // Tambahkan event listener untuk setiap tombol add-to-cart
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function () {
            const name = this.getAttribute('data-name');
            const price = this.getAttribute('data-price');
            const image = this.getAttribute('data-image');
            addToCart(name, price, image);
        });
    });
});
</script>

<!-- End of Menu Section -->


    <!--products section-->
    <section class="products" id="products">

        <h1 class="heading"> our <span>products</span></h1>
        <div class="box-container">
            <div class="box">
                <div class="icons">
                    <a href="" class="fas fa-shopping-cart"></a>
                    <a href="" class="fas fa-heart"></a>
                    <a href="" class="fas fa-eye"></a>
                </div>
                <div class="image">
                    <img src="./images/product-1.png" alt="">
                </div>
                <div class="content">
                    <h3>coffee packet</h3>
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <div class="price">₹ 150.99/- <span>₹ 199/-</span></div>
                </div>
            </div>
            <div class="box">
                <div class="icons">
                    <a href="" class="fas fa-shopping-cart"></a>
                    <a href="" class="fas fa-heart"></a>
                    <a href="" class="fas fa-eye"></a>
                </div>
                <div class="image">
                    <img src="./images/product-2.png" alt="">
                </div>
                <div class="content">
                    <h3>coffee packet</h3>
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <div class="price">₹ 150.99/- <span>₹ 199/-</span></div>
                </div>
            </div>
            <div class="box">
                <div class="icons">
                    <a href="" class="fas fa-shopping-cart"></a>
                    <a href="" class="fas fa-heart"></a>
                    <a href="" class="fas fa-eye"></a>
                </div>
                <div class="image">
                    <img src="./images/product-3.png" alt="">
                </div>
                <div class="content">
                    <h3>coffee packet</h3>
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <div class="price">₹ 150.99/- <span>₹ 199/-</span></div>
                </div>
            </div>
        </div>
    </section>
    <!--products section-->


    <!--review section-->
    <section class="review" id="review">

        <h1 class="heading"> customer's <span>review</span></h1>
        <div class="box-container">
            <div class="box">
                <img src="./images/quote-img.png" class="quote" alt="">
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facere repudiandae quas qui aperiam, quo, quis a unde vel, necessitatibus voluptatem possimus et aliquid neque modi. Tenetur minima unde laborum sequi!</p>
                <img src="./images/pic-1.png" alt="" class="user">
                <h3>customer 1</h3>
                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                </div>
            </div>
            <div class="box">
                <img src="./images/quote-img.png" class="quote" alt="">
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facere repudiandae quas qui aperiam, quo, quis a unde vel, necessitatibus voluptatem possimus et aliquid neque modi. Tenetur minima unde laborum sequi!</p>
                <img src="./images/pic-2.png" alt="" class="user">
                <h3>customer 2</h3>
                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                </div>
            </div>
            <div class="box">
                <img src="./images/quote-img.png" class="quote" alt="">
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facere repudiandae quas qui aperiam, quo, quis a unde vel, necessitatibus voluptatem possimus et aliquid neque modi. Tenetur minima unde laborum sequi!</p>
                <img src="./images/pic-3.png" alt="" class="user">
                <h3>customer 3</h3>
                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                </div>
            </div>
        </div>
    </section>
    <!--review section-->


    <!--contact section-->
    <section class="contact" id="contact">

        <h1 class="heading"> contact <span>us</span> </h1>
    
        <div class="row">
    
            <iframe class="map" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d30153.788252261566!2d72.82321484621745!3d19.141690214227783!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7b63aceef0c69%3A0x2aa80cf2287dfa3b!2sJogeshwari%20West%2C%20Mumbai%2C%20Maharashtra%20400047!5e0!3m2!1sen!2sin!4v1629452077891!5m2!1sen!2sin" allowfullscreen="" loading="lazy"></iframe>
    
            <form action="">
                <h3>get in touch</h3>
                <div class="inputBox">
                    <span class="fas fa-user"></span>
                    <input type="text" placeholder="name">
                </div>
                <div class="inputBox">
                    <span class="fas fa-envelope"></span>
                    <input type="email" placeholder="email">
                </div>
                <div class="inputBox">
                    <span class="fas fa-phone"></span>
                    <input type="number" placeholder="phone no">
                </div>
                <input type="submit" value="contact now" class="btn">
            </form>
    
        </div>
    
    </section>
    <!--contact section-->


    <!--blogs section-->
    <section class="blogs" id="blogs">
        <h1 class="heading"> our <span>blogs</span></h1>

        <div class="box-container">
            <div class="box">
                <div class="image">
                    <img src="./images/blog-1.jpeg" alt="">
                </div>
                <div class="content">
                    <a href="" class="title">refreshing coffee benefits</a>
                    <span>by admin / 3rd july, 2003</span>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Eius, cum.</p>
                    <a href="" class="btn">read more</a>
                </div>
            </div>
            <div class="box">
                <div class="image">
                    <img src="./images/blog-2.jpeg" alt="">
                </div>
                <div class="content">
                    <a href="" class="title">refreshing coffee benefits</a>
                    <span>by admin / 3rd july, 2003</span>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Eius, cum.</p>
                    <a href="" class="btn">read more</a>
                </div>
            </div>
            <div class="box">
                <div class="image">
                    <img src="./images/blog-3.jpeg" alt="">
                </div>
                <div class="content">
                    <a href="" class="title">refreshing coffee benefits</a>
                    <span>by admin / 3rd july, 2003</span>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Eius, cum.</p>
                    <a href="" class="btn">read more</a>
                </div>
            </div>
        </div>
    </section>
    <!--blogs section-->


    <!--footer-->
    <section class="footer">

        <div class="share">
            <a href="https://pritam-sarbajna.netlify.app/" class="fa-solid fa-earth-asia"></a>
            <a href="https://github.com/PritamSarbajna" class="fa-brands fa-github"></a>
            <a href="https://www.linkedin.com/in/pritam-sarbajna-74945821b/" class="fa-brands fa-linkedin"></a>
        </div>

        <div class="links">
            <a href="#home">home</a>
            <a href="#about">about</a>
            <a href="#menu">menu</a>
            <a href="#products">products</a>
            <a href="#review">review</a>
            <a href="#contact">contact</a>
            <a href="#blogs">blogs</a>
        </div>
        
        <div class="credit">Created By <span>Pritam Sarbajna</span>  | <i class="far fa-copyright"></i> 2022 All rights reserved.</div>
    </section>
    <!--footer-->







    <script src="js/script.js"></script>
</body>
</html>
