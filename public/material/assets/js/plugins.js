const baseUrl = window.location.origin;

// Cek apakah ada elemen yg butuh plugin
if (
  document.querySelector("[toast-list]") ||
  document.querySelector("[data-choices]") ||
  document.querySelector("[data-provider]")
) {
    // function untuk load script eksternal/internal
    function loadScript(src) {
        const script = document.createElement("script");
        script.src = src;
        script.async = true; // biar non-blocking
        document.head.appendChild(script);
    }

    // load toastify dari CDN
    loadScript("https://cdn.jsdelivr.net/npm/toastify-js");

    // load choices.js dari local
    loadScript(`${baseUrl}/material/assets/libs/choices.js/public/assets/scripts/choices.min.js`);

    // load flatpickr dari local
    loadScript(`${baseUrl}/material/assets/libs/flatpickr/flatpickr.min.js`);
}
