# ROUND BREAKER

#### Proyecto final Desarrollo de Aplicaciones Web
#### Grupo DW2E 2020
#### I.E.S. Clara del Rey

## Autores
- Lucas Ariel González Morales
- Borja González Pereiro
- Pedro González-Tarrío Polo

# Descripción del proyecto

## ¿Qué es Round Breaker?

Round Breaker es un sistema de gestión de competiciones de e-sports que abarca inscripciones, formaciones de equipos y gestión de rondas y resultados, además de integración con el sistema de retransmisión Twitch y con su chat.

Este sistema pretende abarcar un nicho poco atendido de la comunidad de deportes electrónicos como es el nivel no profesional, centrándose más en la característica social de los torneos, agrupando jugadores de forma aleatoria y poniéndolos en contacto entre ellos, resultando en competiciones informales y desenfadadas.

Es una aplicación web desde donde los usuarios pueden registrarse, gestionar su perfil e inscripciones. Cada usuario puede también organizar competiciones, obteniendo todos los permisos sobre ellas a través de un panel de organizador para ver los detalles y editar las opciones y resultados. Desde este panel, el organizador se conectará con el chat de Twitch del canal indicado donde los jugadores inscritos deben confirmar que están presentes para marcar su registro en la competición como confirmado.

Una vez haya suficientes inscripciones confirmadas el organizador podrá generar los equipos, las rondas y el árbol de competición a través de un solo botón. La competición entonces se marcará como cerrada y todos los jugadores que hayan sido elegidos aleatoriamente para participar podrán acceder a los datos de su equipo para ponerse en contacto con sus compañeros. Podrán ver, además, las credenciales necesarias para unirse a la sala del juego donde se disputarán los encuentros. En cada equipo, uno de los jugadores será elegido como capitán y representará a su equipo, siendo además el único que puede cambiar el nombre del equipo.

Desde la aplicación web podrán consultarse públicamente los resultados y un histórico de competiciones de los usuarios.

## Idea

La idea de Round Breaker surge de observar el crecimiento exponencial de los e-sports y cómo han aparecido muchas herramientas enfocadas a competiciones online pero siempre pensando desde un punto de vista profesional, con equipos ya formados y de buen nivel.

Nosotros queremos ofrecer un enfoque distinto, orientado a competiciones más informales y fortaleciendo el componente social creando equipos en vivo y poniendo en contacto a sus integrantes.

Round Breaker provee una integración completa con la principal plataforma de retransmisión de e-sports del mundo: Twitch. Los participantes confirman su inscripción desde el chat del propio Twitch para que el organizador tenga visitas garantizadas.

Nuestra aplicación ofrece una forma rápida y ágil de organizar todo lo necesario para que pueda hacerse en vivo mientras se retransmite, para hacerlo así también atractivo para el espectador.

# Instalación

## Requisitos

- PHP >= 7.4.2
- Composer
- MySQL >= 5.7

## Paso a paso

### Clonación del proyecto
Puedes clonar todo nuestro proyecto desde nuestro repositorio público de github [github.com/bgonp/round-breaker](https://github.com/bgonp/round-breaker):
```
$ git clone https://github.com/bgonp/round-breaker
```
Asegúrate que la carpeta `/var` tiene permisos de lectura y escritura, ya que es necesario para el uso de caché y log. 

#### Instalación de dependencias
Una vez con el repositorio clonado puedes instalar las dependencias necesarias usando composer ejecutando este comando desde la raíz del proyecto:
```
$ composer install
```
#### Base de datos
La aplicación necesita una base de datos MySQL para funcionar (versión mínima 5.7). Crea una base de datos y un usuario con permisos de lectura y escritura sobre ella. Necesitarás el nombre de la base de datos, el nombre de usuario y la contraseña para poder conectar la aplicación.

#### E-mail
Para el sistema de recuperación de contraseña necesitas una cuenta de correo electrónico y sus credenciales de autenticación para correo saliente.

#### Configuración del entorno
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

#### Inicializar la base de datos
El siguiente paso es ejecutar las migraciones del proyecto para crear todas las tablas y relaciones usando el siguiente comando desde la raíz del proyecto:
```
$ php bin/console doctrine:migrations:migrate
```

#### Datos de prueba
Puedes cargar datos de prueba en la aplicación para ver su funcionamiento ejecutando el siguiente comando (solo en entorno dev y test):
```
$ php bin/console doctrine:fixtures:load
```
La contraseña inicial de todos los usuarios generados (incluido admin) es `randompassword`

#### Usuario administrador
Si no cargas los datos de prueba, puedes generar en base de datos un primer usuario con derechos de administrador a través de la ruta `/install`. Aquí aparece un formulario si en la base de datos no existe ningún usuario.

#### Tests
Existe una carpeta `tests` con una batería de tests que puedes ejecutar para comprobar el correcto funcionamiento de la aplicación.

#### Servidor
El servidor que aloje la aplicación debe configurarse para que apunte a la carpeta `public/` que está en la raíz del proyecto. Se necesita el módulo rewrite para atender correctamente las peticiones a las rutas. Dejamos una configuración de ejemplo para servidores Apache, es necesaria la instrucción `AllowOverride All` para usar el .htaccess ubicado en dicha carpeta `public/`:
```
<VirtualHost *:80>
   DocumentRoot /var/www/round-breaker/public
   <Directory /var/www/round-breaker/public>
       AllowOverride All
       Require all granted
   </Directory>
</VirtualHost>
```

# Diseño de base de datos

### Diagrama entidad relación
![diagrama entidad relación](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/DB_ER_model.png)

### Diagrama lógico
![Diagrama lógico de base de datos](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/DB_logical_model.png)

## Descripción
Definimos las entidades que forman el proyecto y sus relaciones. Hemos definido una clase abstracta `Base` de la que heredan el resto de entidades. Esta clase contiene los atributos que son comunes a todas las demás clases (`id`, `createdAt`, `updatedAt`) y un método `equals` para comparar instancias. Además tiene un método que, siguiendo el lifecycle de doctrine _PreUpdate_, actualiza en cada escritura el campo `updatedAt` con la hora y fechas actuales. Con esto hemos conseguido evitar la repetición de código y despejar mucho las entidades. Para el correcto mapeo del ORM hemos tenido que etiquetarla como `MappedSuperclass` y en las  

#### Player
Es la entidad que representa a los usuarios, implementa la interfaz `UserInterface` para ser la entidad sobre la que actúa el sistema de autenticación, implementando los métodos necesarios. El usuario se identifica usando el campo `username` y tanto éste como `email` y `twitch_name` son obligatorios y deben ser únicos (este último debe serlo ya que nuestro sistema de competiciones basa su sistema de confirmaciones en la conexión con el chat de Twitch).

Es importante indicar que cada uno de estos jugadores, puede crear competiciones y actuará como organizador de la misma, teniendo permisos especiales sobre ella (esto no implica que no pueda inscribirse, un usuario puede ser organizador de una competición y participar en la misma).

#### Game
Es una entidad sencilla, que contiene únicamente nombre y descripción de un juego. Se usa como atributo de competición, ya que las competiciones se disputan en juegos concretos. Solo el administrador puede dar de alta nuevos juegos a los que dar soporte y editarlos o borrarlos (borrarlo solo si no existe ya alguna competición de ese juego).

#### Competition
Esta es la entidad más compleja y alrededor de la cual se desarrolla toda la lógica de la aplicación. Además de campos sencillos como el nombre (`name`) o la fecha de celebración de la competición (`heldAt`) podemos ampliar la información de las siguiente propiedades y relaciones:
- Las propiedades booleanas `isOpen` y `isFinished` definen el estado de la competición. Una competición admite inscripciones hasta que se marca `isOpen` a `false` que es cuando se generan los equipos y puede comenzar la competición. Una vez que se ha llegado a la final y se ha declarado un vencedos, la competición para a tener `isFinished` a true y ya no admite más cambios. Resumiendo:
    - `isOpen` true & `isFinished` false: Competición programada y aceptando inscripciones.
    - `isOpen` false & `isFinished` false: Competición en curso, equipos generados y no se admiten más inscripciones.
    - `isOpen` false & `isfinished` true: Competición finalizada, tiene un ganador y no admite más cambios.
- `lobbyName` y `lobbyPassword` contienen las claves necesarias para conectarse a la sala del juego donde se celebrará la competición. Esta información será solo visible por los jugadores seleccionados para participar.
- `playersPerTeam` y `maxPlayers` son dos enteros que definen el tipo de competición a celebrar, pudiendo ser equipos de entre 1 (individual) y 5 jugadores y un árbol de 2, 4, 8 o 16 equipos.
- `streamer` es la relación `ManyToOne` con la entidad `Player` que representa al jugador que ha creado la competición y que actúa de organizador.
- `game` es la relación `ManyToOne` con la entidad que representa el juego sobre el que se disputa la competición.
- `registrations` es la relación `OneToMany` con la entidad que representa la inscripción de cada jugador a esta competición.
- `teams` es la relación `OneToMany` con los equipos generados para esta competición.
- `rounds` es la relación  `OneToMany` con las rondas que componen el árbol de la competición.
- `twitchBotName` y `twitchBotToken` son las credenciales necesarias para conectarse al chat de Twitch del canal indicado en `twitchChannel` y escuchar las confirmaciones de las inscripciones. 

#### Registration
Representa la inscripción de un jugador a una competición. Esta inscripción solo se tendrá en cuenta para la generación de equipos de la competición si se marca la propiedad `isConfirmed` a true. Para hacer esto el usuario debe confirmar que está presente en el directo de Twitch donde se retransmita escribiendo en el chat el comando `!confirmo`, una vez el organizador abra las inscripciones desde el panel de competición.

#### Team
Representa una agrupación puntual de jugadores en un equipo para participar en una competición. Tal y como esta pensado nuestro sistema, siendo inscripciones individuales y equipos aleatorios, un equipo existe únicamente para una competición puntual, es por esto que la relación entre `Team` y `Competition` es  `ManyToOne` en lugar de `ManyToMany`.
Hay dos relaciones entre `Team` y `Player`:
- `captain` es una relación `ManyToOne` que representa a un único capitán de cada equipo. Este capitán será el portavoz del equipo y será el único con derechos para editarlo.
- `players` es una relación `ManyToMany` que representa a cada uno de los integrantes del equipo. Aunque hemos limitado en el código a un máximo de 5 jugadores por equipo, esto se puede modificar fácilmente si el futuro queremos equipos más amplios.

Además tiene el atributo `name` con el nombre del equipo (editable por su capitán), `ranking` que almacena el puesto finan y se actualiza según vayan finalizando las rondas, y una relación `ManyToMany` con las rondas en las que juega este equipo.

#### Round
Representa cada una de las rondas de las que se compone un árbol de competición. Cada ronda enfrenta a dos equipos, pero la relación entre `Round` u `Team` es `ManyToMany` (en lugar de una relación `ManyToOne` para cada equipo) porque queremos dar soporte a encuentros entre un mayor número de equipos, aunque de momento la lógica de nuestra aplicación lo limita a 2.
Las rondas pertenecen a una única `Competition` y se determina su lugar en el árbol mediante dos atributos:
- `bracketLevel`: un entero que representa el nivel en el árbol (cuartos, semifinales, final...)
- `bracketOrder`: un entero que representa el orden de esta ronda dentro de su nivel.

Con estas dos variables se puede ubicar correctamente cada ronda en el árbol y determina cual es la siguiente ronda de cada equipo ganador.
Por último existe otra relación con `Team`, esta vez `ManyToOne` que es un nullable y pasa a apuntar al equipo ganador una vez que ha terminado la ronda. 

#### ResetPasswordRequest
Es una entidad generada por el bundle `SymfonyCastsResetPasswordBundle` que se encarga de almacenar los tokens y su fecha de caducidad cada vez que un usuario pide recuperar su contraseña.

# Páginas
Los controladores se encargan de gestionar las peticiones y devolver respuestas. Hemos recopilado en esta tabla cada una de las rutas existentes en el proyecto con los controladores que las gestionan.

Hemos indicado permisos de player, competitor y streamer aunque todas ellas se refieren a la misma entidad `Player` ya que cada jugador ejerce de `streamer` sobre las competiciones que organiza él mismo y ejerce de `competitor` sobre las inscripciones en las que se registra él mismo o sobre los equipos a los que pertenece. Indicamos `player` en las páginas en las que solo se necesita haber iniciado sesión.

Hemos omitido en esta tabla las páginas de recuperación de contraseña y de instalación por no formar parte de la navegación principal.

|Vista|Permisos|Controlador|Nombre ruta|Descripción|métodos|
| --- | --- | --- | --- | --- | --- |
| SI | public | Main | main | Página principal | GET/POST |
|  |  |  |  |  |  |
| SI | public | Competition | competition_list | Lista de competiciones | GET |
| SI | player | Competition | competition_new | Crear nueva competición | GET/POST |
| NO | streamer | Competition | competition_delete | Borrar una competición | POST |
| SI | streamer | Competition | competition_edit | Editar una competición | GET/POST |
| SI | public | Competition | competition_show | Mostrar detalles de una competición | GET |
|  |  |  |  |  |  |
| NO | player | Registration | registration_new | Crear nueva inscripción | POST |
| NO | competitor | Registration | registration_delete | Borrar una inscripción | POST |
| NO | streamer | Registration | toggle_confirmation | Confirmar/Desconfirmar una inscripción | POST |
|  |  |  |  |  |  |
| SI | public | Game | game_list | Lista de juegos | GET |
| SI | public | Game | game_show | Ver detalles de un juego | GET |
| SI | admin | Game | game_new | Crear un nuevo juego | GET/POST |
| NO | admin | Game | game_delete| Borrar un juego existente | POST |
| SI | admin | Game | game_edit | Editar un juego | GET/POST |
|  |  |  |  |  |  |
| SI | competitor | Team | team_show | Detalles del equipo y credenciales | GET/POST |
|  |  |  |  |  |  |
| SI | public | Player | player_show | Información del jugador e histórico | GET |
| SI | admin | Player | player_edit | Edición de datos de jugador | GET/POST |
| SI | player | Player | profile | Edición de datos de jugador e histórico propio | GET/POST |
|  |  |  |  |  |  |
| NO | streamer | Api | api_winner | Indicar el ganador de una ronda | POST |
| NO | streamer | Api | api_confirm | Confirmar una inscripción | POST |
| NO | streamer | Api | open_confirmations | Abrir confirmaciones (inicia la escucha de Twitch) | POST |

En [**este enlace**](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/Endpoints.png) se puede ver una versión más extensa de esta tabla, con las rutas completas, los parámetros que se pasan y la plantilla que renderiza la vista.

### Mapa de navegación
La navegación entre las citadas páginas puede realizarse siguiendo el siguiente mapa de navegación, donde se puede ver las rutas con las que tiene contacto directo cada página. Hemos indicado como restringido (en naranja) las páginas `competition_edit` (que necesita además de estar logueado ser el creador de la competición) y `team_show` (que necesita que seas miembro del equipo que intentas ver).

![mapa de navegación](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/sitemap.png)

# Estructura del proyecto

El proyecto se ha desarrollado en el framework PHP Symfony. Descripción de las principales carpetas y ficheros:

## config
Contiene los archivos de configuración del proyecto. 

## public
Carpeta pública hacía la que van las peticiones y las resuelve a través del fichero index.php, también contiene la configuración del directorio de apache (.htaccess). Todos los recursos públicos se ubican aquí:
- **css/**: Contiene los archivos de estilos de la web. Es un archivo principal main.css y archivos extra para cada tipo de árbol de competición.
- **fonts/**: Contiene las fuentes utilizadas para mostrar los textos de la web (Octin y BrokenGlass)
- **img/**: Imágenes recurso (logo, favicon, imágenes de la home y logos de redes sociales).
- **js/**:
    - **main.js**: Principal funcionamiento y listeners de la interfaz de la web.
    - **twitch.js**: Se carga en la página de edición de competición y se encarga de comunicarse con Twitch usando la librería tmi.js y envía peticiones al servidor para confirmar inscripciones.
    - **bracket.js**: Se carga en las competiciones con rondas generadas y se encarga de enviar al servidor información del resultado de cada ronda y actualizar el árbol.
    - **tmi.min.js**: Librería para comunicarse con Twitch usada por twitch.js.

## src
Carpeta principal con el código fuente de la página, dividida en subcarpetas según la funcionalidad y el tipo de lógica de las clases que contiene:

### Migrations

Contiene las migraciones de Doctrine de la base de datos.
Las migraciones guardan los cambios a la base de datos y nos permiten actualizarla de forma segura a la vez de mantener un historial.
Se pueden ejecutar en orden en una base de datos vacía para tener todas las entidades requeridas por la aplicación.

### Entity

Clases requeridas por Doctrine para convertir nuestras tablas en entidades.
Su función es disminuir la necesidad de queries de SQL y facilitar la adquisición de información.

### Repository

Contiene métodos que hacen peticiones a base de datos.
Utilizados para hacer queries más complicadas y evitar llamadas innecesarias al EntityManager.
Su función es agilizar las peticiones y evitar duplicación de código en los controladores.

### Service

Métodos que editan información de la base de datos.
Llaman a métodos de repositorios y entidades, reciben objetos y los editan de la forma deseada.
Tras la edición de la entidad actualizan la base de datos de la tabla correspondiente.
Sirven para reestructurar y dividir los métodos de la aplicación en clases específicas.

### Controller

Contiene todos los controladores de Symfony, los cuales dan lógica a toda la aplicación. Su función es dar instrucciones a
las rutas de la página web y devolver datos a los modelos twig. Escritos en PHP.

### ArgumentResolver

Contiene clases que implementan la interfaz ParamConverter.
Nos permite resolver entidades mediante IDs introducidos como parámetros en las rutas de la página.
Su función es facilitar la resolución de entidades en los controladores.

### Security

Contiene el autenticador de inicio de sesión de la página web.
Se encarga de manejar el formulario de login y resolver la adquisición del usuario logeado.
La clase implementa la interfaz de autenticación de Symfony.

### DataFixtures

Contiene clases Fixture de Doctrine, los cuales permiten rellenar una base de datos vacía con datos de prueba.
Cada clase contiene métodos que rellenan las tablas de la forma programada.
Su función es crear una base de datos con información realista con gran cantidad de entidades, 
imprescindible para probar la funcionalidad de la página.

### Exception

Excepciones personalizadas de Symfony, nos permiten dar mensajes de error precisos a nuestros métodos en caso de fallo.
Evitan el uso de mensajes de excepción genéricos.

### TwigExtension

Extensiones a la funcionalidad de archivos twig.
Contienen métodos utilizados en modelos, disminuyen la lógica incluida en ellos.
Sirven para mantener las vistas con la menor programación posible.

### Form

Contienen tipos de formularios personalizados creados mediante Symfony.
Implementan la interfaz AbstractType, la cual incluye métodos para crear formularios html en PHP.

## templates

Modelos twig que reciben datos de los controladores y proporcionan una vista a la aplicación.
Cada ruta tiene su propia vista, la cual renderiza los datos adecuadamente.
El uso de archivos twig nos permite enseñar y esconder código html de forma inteligente, 
dependiendo del tipo de usuario y sus permisos.

## tests

# Organización del equipo
El código completo del proyecto se puede consultar en el repositorio de GitHub [bgonp/round-breaker](https://github.com/bgonp/round-breaker), donde puede consultarse tanto el código final como los diferentes commits y ramas que hemos ido haciendo los tres integrantes del equipo.

Hemos dividido el proyecto en áreas generales para organizar el equipo, cada uno de nosotros será el principal encargado de algunas de estas áreas, aunque los tres colaboramos en todas ellas:

- Base de datos (Borja)
- Entidades (Borja)
- Repositorios (Borja)
- Controladores (Lucas)
- Back-end (Lucas)
- Seguridad (Lucas)
- Diseño (Pedro)
- Formularios (Pedro)
- Templates (Pedro)
- Integración con Twitch (Borja)
- Tests (Borja)

# Tecnologías usadas

#### Symfony
El proyecto se ha desarrollado en PHP usando el framework Symfony, comenzando en la versión 5.0.7 y actualizando posteriormente a 5.1.0.

#### DebugBundle
Proporciona herramientas de ayuda durante el desarrollo, accesibles desde la barra mostrada en cada página de la aplicación. Nos ha servido para hacer un seguimiento más profundo de los errores y comprobar el buen funcionamiento y las llamadas a base de datos que se hacían.

#### TwigBundle
Gestor de templates para generar las vistas.

#### DoctrineFixturesBundle
Para generar datos de prueba útiles para probar la aplicación y ejecutar los tests basados en estas fixtures.

#### LiipTestFixturesBundle
Proporciona un trait con herramientas para cargar las fixtures en tiempo de ejecución de la aplicación, usado para cargar los datos de prueba durante la ejecución de los tests.

#### SymfonyCastsResetPasswordBundle
Para crear el sistema de recuperación de contraseñas a través de tokens con caducidad establecida en 2 horas.

#### friendsofphp/php-cs-fixer
Para unificar el estilo del código de los tres integrantes del equipo y prevenir errores aplicamos PHP CS Fixer antes de cada push al repositorio. La configuración de las reglas a aplicar esta en el fichero `.php_cs.dist` en la raíz del proyecto.

#### beberlei/doctrineextensions
Para añadir a las consultas efectuadas en los repositorios funcionalidades extra de MySQL que no están disponibles por defecto desde el query builder, como `RAND()` para devolver registros ordenados aleatoriamente.

#### fzaninotto/faker
Para generar datos aleatorios como nombres de usuario. Lo hemos usado en las fixtures para crear los datos de prueba y al generar equipos para ponerles un nombre inicial.

#### sensio/framework-extra-bundle
Herramientas para controladores, necesario para resolver entidades en los parámetros de los métodos de los controladores cuando la petición contiene un `id`.

#### symfony/mailer
Para enviar correos, usado en el sistema de recuperación de contraseñas.

#### tmijs/tmi.js
Para la integración con el chat de twitch hemos usado la librería [tmi.js](https://github.com/tmijs/tmi.js) para conectar con el websocket del chat de twitch. Usado para confirmar inscripciones de jugadores.

#### bootstrap
Herramienta para diseñar toda la parte front-end de la aplicación web responsive.

#### fontawesome
Set de iconos ligeros para mostrar y ayudar en la navegación.


## Manual de usuario  
Este manual cubrirá las dudas que puedan sugerir al navegar nuestra página por primera vez.  
Empezaremos por la página de inicio.  

![manual-1](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-1.png)
  
Esta es la primera página que verá un nuevo usuario. Desde aquí podrá hacer login/registro y acceder a las 3 ramas principales de la página: **Competiciones**, **Juegos** y su **Perfil**. Además tiene acceso a links a nuestras redes sociales y a los juegos más jugados en la plataforma. Lo competición mostrada en la portada es un torneo elegido aleatoriamente entre todos los torneos abiertos en ese momento.

![manual-2](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-2.png)
![manual-2b](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-2b.png)

Como usuario anónimo se pueden ver todas las competiciones que se están jugando y se han jugado en la página de **Torneos**. Esta disponible el juego de la competición y la fecha en la que se disputará. Desde esta página el usuario puede hacer click y ver una competición con más detalle. Utilizando el filtro, el usuario tambien puede filtrar por juego para tener una lista más específica.

![manual-3](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-3.png)
![manual-4](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-4.png)

Desde la página de un torneo se puede ver el estado de la competición, los brackets actuales, los equipos e inscripciones. Sin hacer login el usuario no podrá unirse a una competición, así que lo haremos antes de continuar.

![manual-5](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-5.png)

Rellenando este formulario el usuario se da de alta en nuestra plataforma. Desde la página de inicio podrá hacer login inmediatamente después. Al estar logeado, podemos ver que tenemos opciones en la página de torneo si nos vamos a una competición **abierta**:

![manual-6](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-6.png)

Ahora se presenta un botón para **Unirse**, si hacemos click en este botón nos inscribiremos en la competición , de esta forma tendremos la oportunidad de competir cuando el creador asigne los equipos aleatoriamente.
Ahora que ya tenemos cuenta, tenemos nuestro **Perfil**:

![manual-7](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-7.png)

En nuestro perfil podemos editar nuestros datos, ver nuestros torneos, inscripciones y equipos.
Accederemos a uno de nuestros equipos para ver más información:

![manual-8](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-8.png)

Aquí tenemos todos los jugadores de nuestro equipo, información sobre el torneo en el que está compitiendo y si somos el capitán del equipo podemos editar el nombre. Además, si nuestro equipo ha terminado su participación en el torneo, podemos ver el ranking en el que hemos quedado.
Continuaremos creando nuestro propio torneo:

![manual-9](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-9.png)

Como podemos ver, ahora hay más opciones en la lista de torneos. Podemos unirnos a competiciones desde aquí y podemos crear una competición dándole al boton correspondiente.

![manual-10](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-10.png)
![manual-11](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-11.png)

Rellenando este formulario podremos tener nuestra propia competición. Desde nuestro perfil podemos acceder a la competición que acabamos de crear.

![manual-12](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-12.png)
![manual-13](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-13.png)

Esta es la página de edición de torneo, aquí podremos editar sus datos, randomizar los equipos, banear/confirmar/desconfirmar jugadores y añadir un **Twitch Bot**. Este bot podrá leer el chat de nuestro canal de Twitch para que nuestros espectadores puedan confirmar su presencia en un torneo mediante un simple mensaje en el chat. Debemos rellenar el formulario así:

![manual-13b](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-13b.png)

Tras abrir confirmaciones en nuestro canal de twitch aparecerá nuestro bot con instrucciones:

![manual-14](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-14.png)
![manual-15](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-15.png)

Una vez estemos contentos las opciones de nuestro torneo y las confirmaciones obtenidas, podemos asignar equipos:

![manual-16](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-16.png)
![manual-17](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-17.png)

De esta forma, ya han sido creados los 4 equipos de nuestro torneo, con jugadores aleatorios. Ahora se pueden ver los equipos con sus capitanes en el lado izquierdo de la pantalla. También podemos hacer click en el nombre del equipo ganador para que avance a la siguiente ronda:

![manual-18](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-18.png)
![manual-19](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-19.png)

Una vez hayamos elegido todos nuestros ganadores, la competición quedará finalizada.

![manual-20](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-20.png)

Esto cubre lo básico para usar nuestra plataforma como jugador o streamer, por lo tanto pasaremos a mostrar las funcionalidades de **admin**.

![manual-21](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-21.png)
![manual-22](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-22.png)

Desde la lista de juegos, podemos ver todos los juegos disponibles en la plataforma. Si hacemos click en uno, podemos ver más información y una lista de todas las competiciones que utilizan ese juego.

Un admin puede crear torneos y editar todos los torneos de la plataforma, no solo los que haya creado él/ella.
Además puede crear, editar y eliminar **Juegos**. Esto es una característica específica de admins. Un usuario normal puede ver la lista de juegos y acceder a ellos, pero no modificarlos de ninguna forma. Si hacemos click en el botón "Crear Juego", se nos presentará este formulario.

![manual-23](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-23.png)
![manual-24](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-24.png)

Tras finalizar el formulario, todos los usuarios podrán utilizar nuestro nuevo juego para sus torneos.

![manual-25](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-25.png)
![manual-26](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-26.png)

Como admin, también podemos editar información de un jugador desde su perfil.

Para concluir este manual de usuario, demostraremos como recuperar nuestra contraseña si la hemos perdido:

![manual-27](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-27.png)
![manual-28](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-28.png)
![manual-29](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-29.png)

Si pinchamos en este enlace, podremos introducir nuestro email para recuperar nuestra cuenta. Tras unos momentos nos llegará un correo a nuestro email:

![manual-30](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-30.png)

Haciendo click en el enlace proporcionado, podremos recuperar nuestra contraseña introduciendo una nueva:

![manual-31](https://raw.githubusercontent.com/bgonp/round-breaker/docs/docs/img/manual/Screenshot-31.png)

Esto concluye nuestro manual de usuario.


