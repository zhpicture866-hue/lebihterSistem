<aside class="{{$layoutData['cssClasses'] ?? 'navbar navbar-vertical navbar-expand-lg'}}"
       @if(config('tablar.layout_light_sidebar') !== null)
           data-bs-theme="{{ config('tablar.layout_light_sidebar') ? 'light' : 'dark' }}"
    @endif
>
    <div class="sidebar-inner">
        <div class="sidebar-header">
            <div class="sidebar-logo-wrapper">
                <a href="/" class="logo-expand">
                    <img src="{{ asset('images/logo-landscape.png') }}" alt="Ruang Kembali">
                </a>
                <a href="/" class="logo-collapse">
                    <img src="{{ asset('images/logo-icon.jpeg') }}" alt="Logo Icon">
                </a>
            </div>
            <button id="sidebarToggle" class="sidebar-toggle-btn d-none d-lg-flex">
                <i class="ti ti-layout-sidebar-left-collapse"></i>
            </button>
        </div>

        <div class="navbar-nav flex-row d-lg-none">
            <div class="nav-item d-none d-lg-flex me-3">
                <div class="btn-list">
                    @include('tablar::partials.header.header-button')
                </div>
            </div>
            <div class="d-none d-lg-flex">
                @include('tablar::partials.header.theme-mode')
                @include('tablar::partials.header.notifications')
            </div>
        </div>

        <div class="sidebar-menu-wrapper" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                @include('partials.menu_item', ['menus' => $menus ?? []])
            </ul>
            {{-- <ul class="navbar-nav pt-lg-3">
                @each('tablar::partials.navbar.dropdown-item',$tablar->menu('sidebar'), 'item')
            </ul> --}}
        </div>
    </div>
</aside>
@push('js')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    const btn = document.getElementById("sidebarToggle");

    function applyResponsiveSidebar() {
        const w = window.innerWidth;

        if (w >= 992 && w <= 1200) {
            // tablet: selalu collapsed (sesuai media query 80px)
            root.classList.add('sidebar-collapsed');
        } else if (w <= 576) {
            root.classList.remove('sidebar-collapsed');
        } else {
            // desktop: ikuti pilihan user
            root.classList.toggle(
                'sidebar-collapsed',
                localStorage.getItem('sidebarCollapsed') === 'true'
            );
        }
    }

    applyResponsiveSidebar();
    window.addEventListener('resize', applyResponsiveSidebar);

    if (!btn) return;

    btn.addEventListener("click", function () {
        root.classList.toggle("sidebar-collapsed");
        localStorage.setItem(
            "sidebarCollapsed",
            root.classList.contains("sidebar-collapsed")
        );
    });
});
</script>
{{-- <script>
document.addEventListener("mousemove", function(e) {
    document.documentElement.style.setProperty(
        "--mouse-y",
        e.clientY + "px"
    );
});
</script> --}}

<script>
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.has-dropdown').forEach(item => {

        const submenu = item.querySelector('.submenu');

        if (!submenu) return;

        let timeout;

        function showMenu() {

            if (
                !document.documentElement.classList.contains('sidebar-collapsed')
                || window.innerWidth <= 576
            ) {
                return;
            }

            clearTimeout(timeout);
            item.classList.add('floating-active');
            const rect = item.getBoundingClientRect();

            // reset dulu
            submenu.style.top = '0px';

            submenu.classList.add('show-floating');

            // tinggi submenu
            const submenuHeight = submenu.offsetHeight;

            // posisi default
            let top = rect.top;

            // viewport
            const viewportHeight = window.innerHeight;

            // kalau kebawah keluar layar
            if (top + submenuHeight > viewportHeight - 20) {

                top = viewportHeight - submenuHeight - 20;

            }

            // jangan minus
            if (top < 10) {
                top = 10;
            }

            // submenu.style.top = top + 'px';
            submenu.style.top = `${top}px`;
            submenu.style.left = `${rect.right + 8}px`;

            const submenuWidth = submenu.offsetWidth;

            if(rect.right + submenuWidth > window.innerWidth){
                submenu.style.left =
                    (window.innerWidth - submenuWidth - 10) + 'px';
            }
        }

        function hideMenu() {
            timeout = setTimeout(() => {

                submenu.classList.remove('show-floating');
                item.classList.remove('floating-active');

            }, 200);

        }
        const hoverCapable = window.matchMedia('(hover: hover)').matches;
        const link = item.querySelector(':scope > .nav-link');

        if (!hoverCapable) {
            link.addEventListener('click', function (e) {
                if (!document.documentElement.classList.contains('sidebar-collapsed')) return;
                e.preventDefault();

                const isOpen = submenu.classList.contains('show-floating');

                // tutup floating lain
                document.querySelectorAll('.submenu.show-floating').forEach(el => el.classList.remove('show-floating'));
                document.querySelectorAll('.floating-active').forEach(el => el.classList.remove('floating-active'));

                if (!isOpen) showMenu();
            });
        }
        if (window.innerWidth > 576 && hoverCapable) {
            item.addEventListener('mouseenter', showMenu);
            submenu.addEventListener('mouseenter', () => clearTimeout(timeout));
            item.addEventListener('mouseleave', hideMenu);
            submenu.addEventListener('mouseleave', hideMenu);
        }
    });

});
</script>
<script>
document.addEventListener('click', function(e){

    // klik area luar sidebar
    if(!e.target.closest('.navbar-vertical')){

        document.querySelectorAll('.submenu.show-floating')
            .forEach(el => {

                el.classList.remove('show-floating');

            });

        document.querySelectorAll('.floating-active')
            .forEach(el => {

                el.classList.remove('floating-active');

            });

    }

});
</script>
@endpush
<style>

@media (min-width: 992px) and (max-width: 1200px) {

    .navbar.navbar-vertical.navbar-expand-lg{
        width:80px;
        z-index:300;
    }

    .page-wrapper{
        margin-left:80px;
    }

    .nav-link-title,
    .logo-expand,
    .submenu-arrow{
        display:none !important;
    }

    .logo-collapse{
        display:block !important;
    }

    /* floating submenu */
    .sidebar-collapsed .submenu{

        position:fixed !important;

        left:88px !important;

        width:230px !important;

        min-width:230px !important;
        max-width:230px !important;

        background:#fff;

        border-radius:12px;

        padding:8px;

        box-shadow:0 10px 30px rgba(0,0,0,.12);

        z-index:9999;

        opacity:0;
        visibility:hidden;

        pointer-events:none;

        overflow-y:auto;
        overflow-x:hidden;

        max-height:calc(100vh - 20px);
    }

    .sidebar-collapsed .submenu.show-floating{
        opacity:1;
        visibility:visible;
        pointer-events:auto;
    }

    #sidebarToggle { display: none !important; }
    .sidebar-collapsed .navbar-nav > .nav-item > .nav-link::before{
        display:none !important;
    }

}

@media (max-width: 576px) {

    .navbar.navbar-vertical.navbar-expand-lg {

        position: fixed !important;

        top: 0;
        left: 0;

        transform: translateX(-100%);

        transition: .3s ease;

        width: 260px !important;

        height: 100vh;

        z-index: 104;

        overflow-y: auto;
    }

    .navbar.navbar-vertical.navbar-expand-lg.mobile-open {
        transform: translateX(0);
    }

    .page-wrapper {
        margin-left: 0 !important;
    }

    /* title tampil */
    .nav-link-title {
        display: inline !important;
    }

    /* arrow tampil */
    .submenu-arrow{
        display:block !important;
    }

    /* logo expand tampil lagi */
    .logo-expand{
        display:block !important;
    }

    .logo-collapse{
        display:none !important;
    }

    /* submenu mobile jadi accordion */
    .submenu{

        position: static !important;

        width: 100% !important;

        left: unset !important;
        top: unset !important;

        background: transparent !important;

        box-shadow: none !important;

        border-radius: 0 !important;

        padding: 0 !important;

    }

    .sidebar-collapsed .submenu.show-floating{

        display:block !important;

        position: fixed;

        left:72px;

        width:220px;

        background:#fff;

        border-radius:10px;

        box-shadow:0 10px 30px rgba(0,0,0,.08);

        z-index:105;

        padding:8px;
    }

    .sidebar-menu-wrapper .navbar-nav::after{
        content: "";
        display: block;
        margin: 20px 16px 0;
        border-top: 1px solid #e9ecef;
    }

}

</style>