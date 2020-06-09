# ROUND BREAKER

#### Proyecto final Desarrollo de Aplicaciones Web
#### Grupo DW2E 2020
#### I.E.S. Clara del Rey

## Autores
- Lucas Ariel González Morales
- Borja González Pereiro
- Pedro González-Tarrío Polo

## Descripción del proyecto

### ¿Qué es Round Breaker?

Round Breaker es un sistema de gestión de competiciones de e-sports que abarca inscripciones, formaciones de equipos y gestión de rondas y resultados, además de integración con el sistema de retransmisión Twitch y con su chat.

Este sistema pretende abarcar un nicho poco atendido de la comunidad de deportes electrónicos como es el nivel no profesional, centrándose más en la característica social de los torneos, agrupando jugadores de forma aleatoria y poniéndolos en contacto entre ellos, resultando en competiciones informales y desenfadadas.

Es una aplicación web desde donde los usuarios pueden registrarse, gestionar su perfil e inscripciones. Cada usuario puede también organizar competiciones, obteniendo todos los permisos sobre ellas a través de un panel de organizador para ver los detalles y editar las opciones y resultados. Desde este panel, el organizador se conectará con el chat de Twitch del canal indicado donde los jugadores inscritos deben confirmar que están presentes para marcar su registro en la competición como confirmado.

Una vez haya suficientes inscripciones confirmadas el organizador podrá generar los equipos, las rondas y el árbol de competición a través de un solo botón. La competición entonces se marcará como cerrada y todos los jugadores que hayan sido elegidos aleatoriamente para participar podrán acceder a los datos de su equipo para ponerse en contacto con sus compañeros. Podrán ver, además, las credenciales necesarias para unirse a la sala del juego donde se disputarán los encuentros. En cada equipo, uno de los jugadores será elegido como capitán y representará a su equipo, siendo además el único que puede cambiar el nombre del equipo.

Desde la aplicación web podrán consultarse públicamente los resultados y un histórico de competiciones de los usuarios.

### Idea

La idea de Round Breaker surge de observar el crecimiento exponencial de los e-sports y cómo han aparecido muchas herramientas enfocadas a competiciones online pero siempre pensando desde un punto de vista profesional, con equipos ya formados y de buen nivel.

Nosotros queremos ofrecer un enfoque distinto, orientado a competiciones más informales y fortaleciendo el componente social creando equipos en vivo y poniendo en contacto a sus integrantes.

Round Breaker provee una integración completa con la principal plataforma de retransmisión de e-sports del mundo: Twitch. Los participantes confirman su inscripción desde el chat del propio Twitch para que el organizador tenga visitas garantizadas.

Nuestra aplicación ofrece una forma rápida y ágil de organizar todo lo necesario para que pueda hacerse en vivo mientras se retransmite, para hacerlo así también atractivo para el espectador.

## Instalación

### Requisitos

- PHP >= 7.4.2
- Composer
- MySQL >= 5.7

### Paso a paso

#### - Clonación del proyecto
Puedes clonar todo nuestro proyecto desde nuestro repositorio público de github [github.com/bgonp/round-breaker](https://github.com/bgonp/round-breaker):
```
$ git clone https://github.com/bgonp/round-breaker
```

#### - Instalación de dependencias
Una vez con el repositorio clonado puedes instalar las dependencias necesarias usando composer ejecutando este comando desde la raíz del proyecto:
```
$ composer install
```
#### - Base de datos
La aplicación necesita una base de datos MySQL para funcionar. Crea una base de datos y un usuario con permisos de lectura y escritura sobre ella. Necesitarás el nombre de la base de datos, el nombre de usuario y la contraseña para poder conectar la aplicación.

#### - E-mail
Para el sistema de recuperación de contraseña necesitas una cuenta de correo electrónico y sus credenciales de autenticación para correo saliente.

#### - Configuración del entorno
En la raíz del proyecto existe un archivo `.env` que contiene las variables de entorno. Copia este fichero y renómbralo a `.env.local` y sustituye aquí los valores necesarios:
- Entorno de producción (dev para entorno de desarrollo):
```
APP_ENV=prod
```
- Correo saliente:
```
MAILER_DSN=smtps://cuenta:contraseña@servidor:puerto
```
- Base de datos:
```
DATABASE_URL=mysql://usuario:contraseña@servidor:puerto/nombre_db?serverVersion=5.7
```

#### - Inicializar la base de datos
El siguiente paso es ejecutar las migraciones del proyecto para crear todas las tablas y relaciones usando el siguiente comando desde la raíz del proyecto:
```
$ php bin/console doctrine:migrations:migrate
```

#### - Datos de prueba
Puedes cargar datos de prueba en la aplicación para ver su funcionamiento ejecutando el siguiente comando (no hacer esto en entorno de producción):
```
$ php bin/console doctrine:fixtures:load
```

#### - Tests
Existe una carpeta `tests` con una batería de tests que puedes ejecutar para comprobar el correcto funcionamiento de la aplicación.

#### - Servidor
El servidor que aloje la aplicación debe configurarse para que apunte a la carpeta `public/` que está en la raíz del proyecto. Se necesita el módulo rewrite para atender correctamente las peticiones a las rutas. Dejamos una configuración de ejemplo para servidores Apache:
```
<VirtualHost *:80>
   DocumentRoot /var/www/round-breaker/public
   <Directory /var/www/round-breaker/public>
       Options Indexes FollowSymLinks MultiViews
       AllowOverride All
       Require all granted
   </Directory>
</VirtualHost>
```

## Tecnologías usadas
El proyecto se ha desarrollado en PHP usando el framework Symfony, comenzando en la versión 5.0.8 y migrando posteriormente a 5.1.0.
Para la integración con el chat de twitch hemos usado la librería [tmi.js](https://github.com/tmijs/tmi.js) para conectar con el websocket del chat de twitch.


## Organización del proyecto
  
## Manual de usuario  
  
Este manual cubrirá las dudas que puedan sugerir al navegar nuestra página por primera vez.  
Empezaremos por la página de inicio.  

###screenshot 1
  
Esta es la primera página que verá un nuevo usuario. Desde aquí podrá hacer login/registro y acceder a las 3 ramas principales de la página: **Competiciones**, **Juegos** y su **Perfil**. Además tiene acceso a links a nuestras redes sociales y a los juegos más jugados en la plataforma. Lo competición mostrada en la portada es un torneo elegido aleatoriamente entre todos los torneos abiertos en ese momento.

###screenshot 2-19

Como usuario anónimo se pueden ver todas las competiciones que se están jugando y se han jugado en la página de **Torneos**. Esta disponible el juego de la competición y la fecha en la que se disputará. Desde esta página el usuario puede hacer click y ver una competición con más detalle. Utilizando el filtro, el usuario tambien puede filtrar por juego para tener una lista más específica.


###screenshots 3 - 4

Desde la página de un torneo se puede ver el estado de la competición, los brackets actuales, los equipos e inscripciones. Sin hacer login el usuario no podrá unirse a una competición, así que lo haremos antes de continuar.

###screenshot 5

Rellenando este formulario el usuario se da de alta en nuestra plataforma. Desde la página de inicio podrá hacer login inmediatamente después. Al estar logeado, podemos ver que tenemos opciones en la página de torneo si nos vamos a una competición **abierta**:

###screenshot 6

Ahora se presenta un botón para **Unirse**, si hacemos click en este botón nos inscribiremos en la competición , de esta forma tendremos la oportunidad de competir cuando el creador asigne los equipos aleatoriamente.
Ahora que ya tenemos cuenta, tenemos nuestro **Perfil**:

###screenshot 7

En nuestro perfil podemos editar nuestros datos, ver nuestros torneos, inscripciones y equipos.
Accederemos a uno de nuestros equipos para ver más información:

###screenshot 8

Aquí tenemos todos los jugadores de nuestro equipo, información sobre el torneo en el que está compitiendo y si somos el capitán del equipo podemos editar el nombre. Además, si nuestro equipo ha terminado su participación en el torneo, podemos ver el ranking en el que hemos quedado.
Continuaremos creando nuestro propio torneo:

###screenshot 9

Como podemos ver, ahora hay más opciones en la lista de torneos. Podemos unirnos a competiciones desde aquí y podemos crear una competición dándole al boton correspondiente.

###screenshots 10-11

Rellenando este formulario podremos tener nuestra propia competición. Desde nuestro perfil podemos acceder a la competición que acabamos de crear.

###screenshots 12-13

Esta es la página de edición de torneo, aquí podremos editar sus datos, randomizar los equipos, banear/confirmar/desconfirmar jugadores y añadir un **Twitch Bot**. Este bot podrá leer el chat de nuestro canal de Twitch para que nuestros espectadores puedan confirmar su presencia en un torneo mediante un simple mensaje en el chat. Una vez estemos contentos las opciones de nuestro torneo, podemos asignar equipos:

###screenshots 14-15

De esta forma, ya han sido creados los 4 equipos de nuestro torneo, con jugadores aleatorios. Ahora se pueden ver los equipos con sus capitanes en el lado izquierdo de la pantalla. También podemos hacer click en el nombre del equipo ganador para que avance a la siguiente ronda:

###screenshots 16-17

Una vez hayamos elegido todos nuestros ganadores, la competición quedará finalizada.

###screenshot 18

Esto cubre lo básico para usar nuestra plataforma como jugador o streamer, por lo tanto pasaremos a mostrar las funcionalidades de **admin**.

###screenshot 20-21

Desde la lista de juegos, podemos ver todos los juegos disponibles en la plataforma. Si hacemos click en uno, podemos ver más información y una lista de todas las competiciones que utilizan ese juego.

Un admin puede crear torneos y editar todos los torneos de la plataforma, no solo los que haya creado él/ella.
Además puede crear, editar y eliminar **Juegos**. Esto es una característica específica de admins. Un usuario normal puede ver la lista de juegos y acceder a ellos, pero no modificarlos de ninguna forma. Si hacemos click en el botón "Crear Juego", se nos presentará este formulario.

###screenshots 22-23

Tras finalizar el formulario, todos los usuarios podrán utilizar nuestro nuevo juego para sus torneos.

###screenshot 24-25

Como admin, también podemos editar información de un jugador desde su perfil.

Para concluir este manual de usuario, demostraremos como recuperar nuestra contraseña si la hemos perdido:

###screenshots 26-27-28

Si pinchamos en este enlace, podremos introducir nuestro email para recuperar nuestra cuenta. Tras unos momentos nos llegará un correo a nuestro email:

###screenshot 29

Haciendo click en el enlace proporcionado, podremos recuperar nuestra contraseña introduciendo una nueva:

###screenshot 30

Esto concluye nuestro manual de usuario.


