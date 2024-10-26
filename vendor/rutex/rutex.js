console.log("RuteX.js cargado con éxito");

  //cargador de pwa (tambien se puede agregar al final del body)
  const pwa = document.createElement("link");
  pwa.rel   = "manifest";
  pwa.href  = "/static/pwa/manifest.json";
  document.head.appendChild(pwa);

  //registrar el worker
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('/static/pwa/service-worker.js').then(function(registration) {
        console.log('Service Worker registrado con éxito:', registration);
      }, function(error) {
        console.log('Service Worker fallo en el registro:', error);
      });
    });
  }

//Funciones de RuteX
let rutex = {
    login: () => {

        const diag = document.createElement("dialog");
        diag.style = "border: none; border-radius: 8px; box-shadow: 0 0px 0px rgba(0, 0, 0, 0.1)";
        
        const frame  = document.createElement("iframe");
        frame.height = "245vh";
        frame.src    = "<?=$conax_server?>/user/login?returnto=" + window.location;
        frame.style  = "border:none; overflow:hidden";

        diag.appendChild(frame);
        document.body.appendChild(diag);

        document.body.style = "background-color: rgba(0, 0, 0, 0.75);";
        diag.showModal();
    }
}
