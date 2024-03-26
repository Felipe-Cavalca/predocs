# Dockerfile.app
FROM httpd:2.4
COPY ./App /usr/local/apache2/htdocs
COPY ./App/httpd.conf /usr/local/apache2/conf/my-httpd.conf
RUN echo "Include /usr/local/apache2/conf/my-httpd.conf" >> /usr/local/apache2/conf/httpd.conf
