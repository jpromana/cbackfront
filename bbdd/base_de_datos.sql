CREATE DATABASE IF NOT EXISTS curso_backfront;
USE curso_backfront;

CREATE TABLE users(
    id          int(255) AUTO_INCREMENT NOT NULL, 
    role        varchar(20), 
    name        varchar(255),
    surname     varchar(180),
    email       varchar(255),
    password    varchar(255),
    created_at  datetime,
    CONSTRAINT pk_users PRIMARY KEY(id)
)ENGINE=InnoDb;

CREATE TABLE tasks (
    id          int(255) AUTO_INCREMENT NOT NULL,
    user_id     int(255) NOT NULL,
    title       varchar(255),
    description text, 
    status      varchar(100),
    created_at  datetime,
    updated_at  datetime,
    CONSTRAINT pk_tasks PRIMARY KEY(id),
    CONSTRAINT fk_tasks FOREIGN KEY(user_id) REFERENCES users(id)
)ENGINE=InnoDb;