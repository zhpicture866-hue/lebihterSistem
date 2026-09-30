<header class="website-header">
    <div class="website-navbar">
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="ti ti-menu-2"></i>
        </button>
        <a href="/" class="website-logo">
            <img src="{{ asset('images/logo-landscape.png') }}" alt="ZH Picture">
        </a>
        <nav class="website-menu" id="websiteMenu">
            <a href="/">HOME</a>
            <a href="#">ARTIKEL</a>
            <a href="#">PACKAGE</a>
            <div class="menu-dropdown">
                <button type="button" class="menu-link">
                    PORTOFOLIO
                    <i class="ti ti-chevron-down"></i>
                </button>
                <div class="mega-menu">
                    <a href="#">Jasa Konstruksi</a>
                    <a href="#">Klinik</a>
                    <a href="#">Toko</a>
                </div>
            </div>
            <a href="{{ route('register') }}">BERGABUNG</a>
            <a href="#">TENTANG KAMI</a>
        </nav>
        <div class="website-icons">
            <a href="#"><i class="ti ti-search"></i></a>
            <a href="#"><i class="ti ti-shopping-bag"></i></a>
            <div class="user-dropdown" id="userDropdown">
                <button class="user-btn" id="userBtn">
                    <i class="ti ti-user"></i>
                </button>
                <div class="user-menu">
                    @guest
                        <a href="{{ route('login') }}">
                            <i class="ti ti-login"></i>
                            Masuk
                        </a>

                        <a href="{{ route('register') }}">
                            <i class="ti ti-user-plus"></i>
                            Daftar
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}">
                            <i class="ti ti-layout-dashboard"></i>
                            Dashboard
                        </a>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit">
                                <i class="ti ti-logout"></i>
                                Logout
                            </button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </div>
    <div class="mobile-overlay" id="mobileOverlay"></div>
</header>
<style>
.website-header{
    position:fixed;
    top:20px;
    left:0;
    width:100%;
    z-index:9999;
}
.website-navbar {
    position: relative;

    width: min(92%, 1500px);
    margin: auto;

    height: 72px;
    padding: 0 32px;

    background: #fff;
    border-radius: 22px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, .15);

    display: flex;
    align-items: center;
}
.website-logo img{
    height:64px;
    position: absolute;
    left: 32px;
    top: 50%;
    transform: translateY(-50%);
}
.website-menu{
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);

    display: flex;
    align-items: center;
    gap: 30px;
}
.website-menu>a,
.menu-link{
    background:none;
    border:none;
    color:#111;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
    letter-spacing:.4px;

    cursor:pointer;

    display:flex;

    align-items:center;

    gap:5px;

    transition:.25s;

}

.website-menu>a:hover,
.menu-link:hover{

    color:#bbbdbf;

}
.website-icons{
    position: absolute;
    right: 32px;
    top: 50%;
    transform: translateY(-50%);

    display: flex;
    align-items: center;
    gap: 28px;
}

.website-icons a,
.user-btn{
    background:none;
    border:none;
    color:#111;
    font-size:28px;
    cursor:pointer;
    text-decoration:none;
}

.user-dropdown{
    position:relative;
}
.mobile-menu-btn{
    display:none;
}
.user-menu{
    position:absolute;
    top:45px;
    right:0;

    width:190px;

    background:#fff;
    border-radius:12px;

    box-shadow:0 15px 35px rgba(0,0,0,.15);

    display:none;

    overflow:hidden;

    z-index:99;
}

.user-menu a,
.user-menu button{

    width:100%;

    padding:14px 18px;

    display:flex;

    align-items:center;

    gap:10px;

    background:none;

    border:none;

    text-align:left;

    color:#111;

    text-decoration:none;

    cursor:pointer;

    font-size:15px;
}

.user-menu a:hover,
.user-menu button:hover{

    background:#f5f5f5;

}

.user-dropdown.open .user-menu{

    display:block;

}
.menu-dropdown{
    position:relative;
}
.mega-menu{
    position:absolute;
    top:55px;

    left:50%;

    transform:translateX(-50%);

    width:360px;

    background:#fff;

    display:none;

    box-shadow:0 15px 35px rgba(0,0,0,.18);

}

.menu-dropdown.open .mega-menu{

    display:block;

}
.mega-menu a{

    display:block;

    padding:18px 28px;

    color:#111;

    text-decoration:none;

    font-weight:600;

    border-bottom:1px solid rgba(0,0,0,.15);

}

.mega-menu a:last-child{

    border-bottom:none;

}

.mega-menu a:hover{

    background:#bbbdbf;

}
@media (min-width: 769px) and (max-width: 1200px) {

    .website-navbar {
        padding: 0 20px;
        gap: 16px;
        justify-content: space-between;
    }

    /* logo masuk alur flex */
    .website-logo {
        flex-shrink: 0;
    }

    .website-logo img {
        position: static;
        transform: none;
        height: 56px;
        width: auto;
        display: block;
    }

    /* menu masuk alur flex, tidak lagi di-center absolut */
    .website-menu {
        position: static;
        transform: none;
        flex: 1;
        justify-content: center;
        gap: 16px;
        min-width: 0;
    }

    .website-menu > a,
    .menu-link {
        font-size: 12px;
        white-space: nowrap;
    }

    .website-icons {
        position: static;
        transform: none;
        flex-shrink: 0;
        gap: 10px;
    }

    .website-icons a,
    .user-btn {
        font-size: 22px;
    }
}
@media (max-width: 768px) {

    .website-header {
        top: 12px;
    }

    .website-navbar {
        width: calc(100% - 28px);
        height: 68px;
        padding: 0 16px;

        display: grid;
        grid-template-columns: 44px 1fr 44px;
        align-items: center;

        border-radius: 30px;
    }

    /* HAMBURGER */
    .mobile-menu-btn {
        grid-column: 1;

        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        width: 44px;
        height: 44px;

        padding: 0;
        border: none;
        background: transparent;

        font-size: 30px;
        line-height: 1;

        cursor: pointer;
        z-index: 10003;
    }

    /* LOGO */
    .website-logo {
        grid-column: 2;

        display: flex;
        justify-content: center;
        align-items: center;

        position: static;
    }

    .website-logo img {
        position: static;
        transform: none;

        height: 64px;
        width: auto;
        display: block;
    }

    /* USER ICON */
    .website-icons {
        grid-column: 3;

        position: static;
        transform: none;

        display: flex;
        justify-content: flex-end;
        align-items: center;
    }

    .website-icons > a {
        display: none;
    }

    .website-icons .user-btn {
        font-size: 30px;
    }

    /* MOBILE MENU */
    .website-menu {
        position: fixed;

        top: 0;
        left: -340px;
        transform: none;
        width: 340px;
        max-width: 85vw;
        height: 100vh;

        padding: 90px 24px 30px;

        background: #fff;

        display: flex;
        flex-direction: column;
        align-items: stretch;

        overflow-y: auto;

        box-shadow: 8px 0 30px rgba(0, 0, 0, .15);

        transition: left .35s ease;

        z-index: 10002;
    }

    .website-menu.active {
        left: 0;
    }

    .website-menu > a,
    .website-menu .menu-link {
        display: flex;
        justify-content: space-between;
        align-items: center;

        width: 100%;

        padding: 16px 0;

        font-size: 14px;
        font-weight: 600;
        text-align: left;

        border-bottom: 1px solid #f1f1f1;
    }

    /* OVERLAY */
    .mobile-overlay {
        position: fixed;
        inset: 0;

        background: rgba(0, 0, 0, .45);

        opacity: 0;
        visibility: hidden;

        transition: opacity .3s ease;

        z-index: 10001;
    }

    .mobile-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    /* MEGA MENU */
    .mega-menu {
        display: none;

        position: static;
        left: auto;
        top: auto;
        transform: none;

        width: calc(100% - 16px);

        margin: 10px 0 12px 16px;

        padding: 8px 0;

        background: #f8f8f8;

        border-radius: 0 10px 10px 0;

        box-shadow: none;
        overflow: hidden;
    }

    .menu-dropdown.open .mega-menu {
        display: block;
    }

    .mega-menu a {
        display: block;

        padding: 12px 18px;

        margin: 0;

        font-size: 13px;
        color: #555;

        border: none;
    }
}
</style>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const menu = document.getElementById("websiteMenu");
    const btn = document.getElementById("mobileMenuBtn");
    const overlay = document.getElementById("mobileOverlay");

    if (!menu || !btn || !overlay) {
        console.error("Navbar mobile element tidak ditemukan.");
        return;
    }

    btn.addEventListener("click", function () {
        menu.classList.toggle("active");
        overlay.classList.toggle("show");
    });

    overlay.addEventListener("click", function () {
        menu.classList.remove("active");
        overlay.classList.remove("show");
    });

    document.querySelectorAll(".menu-link").forEach(button => {

        button.addEventListener("click", function (e) {
            e.preventDefault();

            this.closest(".menu-dropdown")
                .classList.toggle("open");
        });

    });

    const userBtn = document.getElementById("userBtn");
    const userDropdown = document.getElementById("userDropdown");

    if (userBtn && userDropdown) {

        userBtn.addEventListener("click", function (e) {
            e.stopPropagation();

            userDropdown.classList.toggle("open");
        });

        document.addEventListener("click", function () {
            userDropdown.classList.remove("open");
        });

    }

    window.addEventListener("resize", function () {

        if (window.innerWidth > 768) {

            menu.classList.remove("active");
            overlay.classList.remove("show");

            document.querySelectorAll(".menu-dropdown")
                .forEach(item => {
                    item.classList.remove("open");
                });

        }

    });

});
</script>