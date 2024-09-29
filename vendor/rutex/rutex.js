console.log("RuteX.js cargado con éxito");

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
