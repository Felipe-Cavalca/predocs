# Dockerfile.app
FROM httpd:2.4

COPY ./app/httpd.conf /usr/local/apache2/conf/my-httpd.conf

RUN echo "LoadModule proxy_module modules/mod_proxy.so" >> /usr/local/apache2/conf/httpd.conf
RUN echo "LoadModule proxy_http_module modules/mod_proxy_http.so" >> /usr/local/apache2/conf/httpd.conf
RUN echo "Include /usr/local/apache2/conf/my-httpd.conf" >> /usr/local/apache2/conf/httpd.conf
