FROM postgres:16-alpine

ENV POSTGRES_DB=medflow_db
ENV POSTGRES_USER=medflow_user
ENV POSTGRES_PASSWORD=medflow_pass

COPY ./database /docker-entrypoint-initdb.d/