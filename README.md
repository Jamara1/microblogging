<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://www.brandemia.org/wp-content/uploads/2012/06/twitter_logo_principal.jpg" width="400" alt="Micrologging Logo"></a></p>

## Sobre Micrologging

Es un proyecto backend muy simplificado de twitter que permite seguir, redactar tweets y ver los tweets de los usuarios seguidos.

## Construcción

- Docker.
- Laravel v11.6.1.
- DynamoDb con Localstack.
- PhpUnitTest.

Microbloggin es un proyecto que utiliza las herramientas mencionadas como parte de requerimiento del cliente para evaluar los entornos de desarrollos que se requieren, laravel es un potente framework que permite trabajar tanto frontend como backend y contiene diferentes plugins que se pueden incorporar para hacer un desarrollo robusto y escalable, se utiliza la base de datos de DynamoDB con Localstack para evitar costos de uso por escritura y consulta permitiendo demostrar el enfoque de la herramienta que permite mockear diferentes servicios de AWS, con un proposito mas orientado a las pruebas unitarias o desarrollos en local sin añadir costo adicional sobre la nube.

## Requerimientos

- [Docker v26.1.3](https://docs.docker.com/)
- [Postman](https://www.postman.com/)

## Instalación

- Copiar el archivo .env.example y a la copia quitar del nombre .env.example a .env
- El proyecto esta configurado con docker-compose.yml para que se despliegue un contenedor con las configuraciones necesarias para ejecutar el proyecto, para ello se requiere ejecutar el comando:

```bash
$ docker compose up -d --build
```
- Para ejecutar las peticiones del proyecto hay un documento en [esta ruta](docs/microblogging.postman_collection.json), se descarga y se importa en postman como una colleción.
- Se puede ver un esquema del proyecto [microblogging.drawio](docs/microblogging.drawio) para visualizar se puede importar en [draw.io](https://app.diagrams.net) o instalar la extension Draw.io integration en Visual Studio Code