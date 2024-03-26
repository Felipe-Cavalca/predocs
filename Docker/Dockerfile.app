# Dockerfile.app
FROM httpd:2.4
COPY ./app /usr/local/apache2/htdocs
COPY ./app/httpd.conf /usr/local/apache2/conf/my-httpd.conf
RUN echo "Include /usr/local/apache2/conf/my-httpd.conf" >> /usr/local/apache2/conf/httpd.conf
