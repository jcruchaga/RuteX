RRRRR  U    U TTTTTTT  EEEEEE X    X 
R    R U    U    T     E       X  X  
RRRRR  U    U    T     EEEEEE   XX   
R  R   U    U    T     E       X  X  
R   RR  UUUU     T     EEEEEE X    X

Novedades de la versión 05.06

1) Se agregaron rutas preseteadas:
- "/ping"  responde con un json que contiene:
			"app_name"    tomado de .ENV
			"app_version" tomado de .ENV
			"remote_addr" IP Address del cliente
			"elap_time"   Tiempo de respuesta del ping (para app Rutex en ambos extremos)

- "/engine" responde con un html que contiene la version de rutex utilizada por esa app.

- Si estamos en modo DEVELOPER (APP_RUN_MODE=DEV en el archivo .env) entonces se agregan las rutas preseteadas:
  "/phpinfo"    que devuelve la configuracion PHP del servidor web, asi como los módulos instalados
  "/playground" ejecuta un php del programador, donde se pueden hacer pruebas de lenguaje, comandos, etc.
                sin tener que modificar objetos de la app.
				        Ubicacion del script: app/views/test/playground.php
				
				
2) Se agregó la funcionalidad de instalación como pwa en mobile.
   El javascript necesario y el worker se cargan automáticamente en la pagina al incluir rutex.js en el head de la pagina home
   Se agregò la carpeta: public/static/pwa que contiene los siguientes archivos:
   2.1) manifest.json  Aqui se debe indicar:
        - El nombre de la app
        - Los iconos pequeño y grande (192x192 y 512x512)
          Se proveen dos iconos de rutex sin copyright, que se pueden usar sin problema
        - Una screenshot de la app que se desee mostrar en el proceso de carga.

   Al ingresar a la app desde un navegador web mobile se ofrece la opción de instalarla como pwa en el dispositivo mobile.





   

