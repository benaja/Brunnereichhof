create database 19911184_rapport2;

use 19911184_rapport2;

create table authorization(id int primary key auto_increment, name text);

create table user(id int primary key auto_increment, email text, username text, firstname text, lastname text, authorization_id int, password text, ismealdefault boolean, remember_token text, isPasswordChanged boolean, updated_at datetime, created_at datetime, foreign key(authorization_id) references authorization(id));

create table customer(id int primary key auto_increment, firstname text, lastname text, addition text, street text, place text, plz text, mobile text, phone text, hasCatering boolean, kitchen_infrastructure text, max_catering int, comment_catering text, driver_info text, comment text, maps text, secret text, customer_number int, needs_payment_order boolean, user_id int, updated_at datetime, created_at datetime, foreign key(user_id) references user(id) on delete cascade);

create table culture(id int primary key auto_increment, name text, updated_at datetime, created_at datetime);

create table hourrecords(id int primary key auto_increment, customer_id int, culture_id int, week int, year int, hours double, comment text, updated_at datetime, created_at datetime, foreign key(customer_id) references customer(id) on delete cascade, foreign key(culture_id) references culture(id) on delete set null);

create table employee(id int primary key auto_increment, callname text, firstname text, lastname text, nationality text, isIntern boolean, isDriver boolean, german_knowledge boolean, english_knowledge boolean, sex text, comment text, experience text, isActive boolean, profileimage text, allergy text, updated_at datetime, created_at datetime);

create table entry_exit(id int primary key auto_increment, employee_id int, date datetime, isEntry boolean, updated_at datetime, created_at datetime, foreign key(employee_id) references employee(id) on delete cascade);

create table workplace(id int primary key auto_increment, name text, updated_at datetime, created_at datetime);

create table workplace_personal(workplace_id int, employee_id int, updated_at datetime, created_at datetime, primary key(workplace_id, employee_id), foreign key(workplace_id) references workplace(id) on delete cascade, foreign key(employee_id) references employee(id) on delete cascade);


create table settings(id int primary key auto_increment, `key` text, value text, type text, updated_at datetime, created_at datetime);

create table timerecord(id int primary key auto_increment, user_id int, date date, lunch boolean, comment text, updated_at datetime, created_at datetime, foreign key(user_id) references user(id) on delete cascade);

create table worktype(id int primary key auto_increment, name varchar(100), name_de varchar(100));

create table hours(id int primary key auto_increment, timerecord_id int, `from` time, `to` time, worktype_id int, comment text, updated_at datetime, created_at datetime, foreign key(timerecord_id) references timerecord(id) on delete set null, foreign key(worktype_id) references worktype(id) on delete set null);

create table rapport(id int primary key auto_increment, customer_id int, isFinished boolean, startdate date, rapporttype varchar(10), comment_mo text, comment_tu text, comment_we text, comment_th text, comment_fr text, comment_sa text,updated_at datetime, created_at datetime, foreign key(customer_id) references customer(id) on delete cascade);

create table project(id int primary key auto_increment, name text, description text, updated_at datetime, created_at datetime);

CREATE TABLE customer_project(project_id int, customer_id int, updated_at datetime, created_at datetime, primary key(project_id, customer_id), foreign key(project_id) references project(id) on delete cascade, foreign key(customer_id) references customer(id) on delete cascade);

CREATE table foodtype(id int primary key auto_increment, foodname text, updated_at datetime, created_at datetime);

CREATE table rapportdetail(id int primary key auto_increment, rapport_id int, project_id int, employee_id int, foodtype_id int, hours double, day int, comment text, date date, updated_at datetime, created_at datetime, foreign key(rapport_id) references rapport(id) on delete cascade, foreign key(project_id) references project(id) on delete set null, foreign key(employee_id) REFERENCES employee(id) on delete cascade, foreign key(foodtype_id) references foodtype(id) on delete cascade);

insert into authorization(name) values('customer'),('admin'),('worker'),('superadmin');

insert into foodtype(foodname) VALUE('eichhof'),('customer'),('none');

insert into worktype(name, name_de) VALUE('productiveHours', 'Produktivstunden'), ('holidays', 'Ferien'), ('sick', 'Krank'), ('accident', 'Unfall');

insert into project(name, description) values('Allgemein', 'Allgemeine arbeiten');

insert into settings(`key`, value, type) value('fullDayShortStart', '08:00', 'string'), ('fullDayShortEnd', '16:00', 'string'), ('fullDayLongStart', '07:00', 'string'), ('fullDayLongEnd', '16:00', 'string');

$2y$10$MJV/WP5/RAc41AbD/kg/xeOvmxixCHfh5B/MReXJu8HMecKEv2CeS