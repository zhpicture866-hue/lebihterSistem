{{-- CSS footer Ruang Kembali. Include SEKALI di <head> layouts/website.blade.php:
     @include('partials.footer-css')
     Lalu HAPUS blok CSS footer yang sama dari <style> welcome.blade.php (opsional, supaya tidak dobel). --}}
<style>
.website-footer{ background:#f8f6f2; padding-top:60px; font-family:'Poppins','Montserrat',sans-serif; overflow:hidden; }
.website-footer img{ max-width:100%; height:auto; }
.footer-info-section{ max-width:1500px; margin:0 auto; padding:0 5% 60px; box-sizing:border-box; }
.footer-info-header{ text-align:center; margin-bottom:40px; }
.footer-info-header h2{ font-size:28px; font-weight:700; color:#111; margin:0 0 8px; }
.footer-info-header p{ color:#666; font-size:14px; margin:0; }
.footer-info-grid{ display:grid; grid-template-columns:1.2fr 1fr; gap:28px; align-items:start; }
.footer-location-card,.footer-contact-card{ background:#fff; border-radius:16px; padding:28px; box-shadow:0 8px 24px rgba(0,0,0,.06); min-width:0; }
.footer-location-card h3,.footer-contact-card h3{ display:flex; align-items:center; gap:8px; font-size:18px; font-weight:700; color:#111; margin:0 0 14px; }
.footer-location-card{ display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:40px 28px; }
.footer-brand-logo{ height:56px; width:auto; margin-bottom:16px; }
.footer-brand-tagline{ font-size:14px; color:#666; max-width:320px; line-height:1.5; margin:0; }
.footer-contact-list{ list-style:none; padding:0; margin:0 0 16px; display:flex; flex-direction:column; gap:14px; }
.footer-contact-list li{ display:flex; align-items:center; gap:10px; font-size:14px; }
.footer-contact-list li i{ font-size:20px; color:#c92a2a; flex-shrink:0; }
.footer-contact-list li a{ color:#333; text-decoration:none; word-break:break-word; }
.footer-contact-list li a:hover{ color:#b7965b; }
.footer-bottom{ text-align:center; padding:20px 5%; border-top:1px solid #eee; font-size:12px; color:#888; margin:0; }
.footer-bottom p{ margin:0; }
@media (max-width:768px){
  .website-footer{ padding-top:40px; }
  .footer-info-grid{ grid-template-columns:1fr; }
  .footer-info-header h2{ font-size:22px; }
}
</style>