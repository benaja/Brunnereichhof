create database 19911184_rapport2;

use 19911184_rapport2;

create table usertype(id int primary key, name text);

create table user(id int primary key auto_increment, email text, username text, firstname text, lastname text, type_id int, role_id int, password text, ismealdefault boolean, remember_token text, isPasswordChanged boolean, isDeleted boolean, updated_at datetime, created_at datetime, foreign key(type_id) references usertype(id), foreign key(role_id) references role(id));

create table customer(id int primary key auto_increment, firstname text, lastname text, addition text, street text, place text, plz text, mobile text, phone text, hasCatering boolean, kitchen_infrastructure text, max_catering int, comment_catering text, driver_info text, comment text, maps text, secret text, customer_number int, needs_payment_order boolean, user_id int, isDeleted boolean, updated_at datetime, created_at datetime, foreign key(user_id) references user(id) on delete cascade);

create table culture(id int primary key auto_increment, name text, isAutocomplete boolean, updated_at datetime, created_at datetime);

create table hourrecords(id int primary key auto_increment, customer_id int, culture_id int, week int, year int, hours double, comment text, createdByAdmin boolean, updated_at datetime, created_at datetime, foreign key(customer_id) references customer(id) on delete cascade, foreign key(culture_id) references culture(id) on delete cascade);

create table employee(id int primary key auto_increment, callname text, firstname text, lastname text, nationality text, isIntern boolean, isDriver boolean, german_knowledge boolean, english_knowledge boolean, sex text, comment text, experience text, isActive boolean, isGuest boolean, profileimage text, allergy text, isDeleted boolean, updated_at datetime, created_at datetime);

create table entry_exit(id int primary key auto_increment, employee_id int, date datetime, isEntry boolean, updated_at datetime, created_at datetime, foreign key(employee_id) references employee(id) on delete cascade);

create table workplace(id int primary key auto_increment, name text, updated_at datetime, created_at datetime);

create table workplace_personal(workplace_id int, employee_id int, updated_at datetime, created_at datetime, primary key(workplace_id, employee_id), foreign key(workplace_id) references workplace(id) on delete cascade, foreign key(employee_id) references employee(id) on delete cascade);


create table settings(id int primary key auto_increment, `key` text, value text, type text, updated_at datetime, created_at datetime);

create table timerecord(id int primary key auto_increment, user_id int, date date, lunch boolean, breakfast boolean, dinner boolean , comment text, updated_at datetime, created_at datetime, foreign key(user_id) references user(id) on delete cascade);

create table worktype(id int primary key auto_increment, name varchar(100), name_de varchar(100), color nvarchar(100), short_name nvarchar(10));

create table hours(id int primary key auto_increment, timerecord_id int, `from` time, `to` time, worktype_id int, comment text, updated_at datetime, created_at datetime, foreign key(timerecord_id) references timerecord(id) on delete set null, foreign key(worktype_id) references worktype(id) on delete set null);

create table project(id int primary key auto_increment, name text, description text, isDeleted boolean, updated_at datetime, created_at datetime);

create table rapport(id int primary key auto_increment, customer_id int, isFinished boolean, startdate date, rapporttype varchar(10), comment_mo text, comment_tu text, comment_we text, comment_th text, comment_fr text, comment_sa text,updated_at datetime, created_at datetime, foreign key(customer_id) references customer(id) on delete cascade, foreign key(default_project) references project(id) on delete set null);

CREATE TABLE customer_project(project_id int, customer_id int, updated_at datetime, created_at datetime, primary key(project_id, customer_id), foreign key(project_id) references project(id) on delete cascade, foreign key(customer_id) references customer(id) on delete cascade);

CREATE table foodtype(id int primary key auto_increment, foodname text, updated_at datetime, created_at datetime);

CREATE table rapportdetail(id int primary key auto_increment, rapport_id int, project_id int, employee_id int, foodtype_id int, hours double, day int, comment text, date date, default_project_id int, updated_at datetime, created_at datetime, foreign key(rapport_id) references rapport(id) on delete cascade, foreign key(project_id) references project(id) on delete set null, foreign key(employee_id) REFERENCES employee(id) on delete cascade, foreign key(foodtype_id) references foodtype(id) on delete cascade);

create table role(id int primary key auto_increment, name nvarchar(100), name_de nvarchar(200), updated_at datetime, created_at datetime);

create table authorizationrule(id int primary key auto_increment, name nvarchar(100), name_de nvarchar(200), updated_at datetime, created_at datetime);

create table role_authorizationrule(role_id int, authorizationrule_id int, primary key(role_id, authorizationrule_id), foreign key(role_id) references role(id), foreign key (authorizationrule_id) references authorizationrule(id));

insert into usertype(id, name) values(1, 'customer'),(2, 'worker'),(3, 'superadmin');

insert into foodtype(foodname) VALUE('eichhof'),('customer'),('none');

insert into worktype(name, name_de) VALUE('productiveHours', 'Produktivstunden'), ('holidays', 'Ferien'), ('sick', 'Krank'), ('accident', 'Unfall'), ('school' 'Schule');

insert into project(name, description) values('Allgemein', 'Allgemeine arbeiten');

insert into settings(`key`, value, type) value('fullDayShortStart', '08:00', 'string'), ('fullDayShortEnd', '16:00', 'string'), ('fullDayLongStart', '07:00', 'string'), ('fullDayLongEnd', '16:00', 'string');

insert into authorizationrule(name, name_de) value
  ('rapport_read', 'Wochenrapport einsehen'),
  ('rapport_write', 'Wochenrapport schreiben'),
  ('employee_preview_read', 'Mitarbeierverzeichnis einsehen (nur Vorschau)'),
  ('employee_read', 'Mitarbeiterverzeichnis einsehen mit Details'),
  ('customer_read', 'Kundenverzeichnis einsehen'),
  ('customer_write', 'Kundenverzeichnis schreiben'),
  ('roomdispositioner_read', 'Raumplaner einsehen'),
  ('roomdispositioner_write', 'Raumplaner schreiben'),
  ('hourrecord_read', 'Sundenangaben einsehen'),
  ('hourrecord_write', 'Sundenangaben schreiben'),
  ('worker_read', 'Hofmitarbeiter einsehen'),
  ('worker_write', 'Hofmitarbeiter schreiben'),
  ('settings_read', 'Einstellungen einsehen'),
  ('settings_write', 'Einstellungen schreiben'),
  ('timerecord', 'Zeiterfassung')

$2y$10$MJV/WP5/RAc41AbD/kg/xeOvmxixCHfh5B/MReXJu8HMecKEv2CeS

/-- Room dispositioner
create table room(id int primary key auto_increment, name nvarchar(100), location nvarchar(100), comment nvarchar(500), number int, isDeleted boolean, updated_at datetime, created_at datetime);

create table bed(id int primary key auto_increment, name nvarchar(100), width nvarchar(100), places int, comment nvarchar(500), isDeleted boolean, updated_at datetime, created_at datetime);

create table size(id int primary key auto_increment, value nvarchar(100), updated_at datetime, created_at datetime);

create table inventar(id int primary key auto_increment, name nvarchar(100), price double, size_id int, updated_at datetime, created_at datetime, foreign key(size_id) references size(id) on delete cascade);

create table bed_inventar(id int primary key auto_increment, bed_id int, inventar_id int, amount int, amount_2 int, foreign key(bed_id) references bed(id) on delete cascade, foreign key(inventar_id) references inventar(id) on delete cascade);

create table bed_room(id int primary key auto_increment, room_id int, bed_id int, foreign key(room_id) references room(id) on delete cascade, foreign key(bed_id) references bed(id) on delete cascade);

create table reservation(id int primary key auto_increment, bed_room_id int, employee_id int, `entry` date, `exit` date, updated_at datetime, created_at datetime, foreign key(bed_room_id) references bed_room(id) on delete cascade, foreign key(employee_id) references employee(id) on delete cascade);


/-- migration script
create table usertype(id int primary key, name text);

insert into usertype(id, name) values(1, 'customer'),(2, 'worker'),(3, 'superadmin');

update user set authorization_id = 2 where authorization_id = 3;
update user set authorization_id = 3 where authorization_id = 4;

alter table user drop foreign key user_ibfk_1;

alter table user change authorization_id type_id int;

drop table authorization;

ALTER TABLE user
add constraint type_id_fk
FOREIGN KEY (type_id) REFERENCES usertype(id);

create table role(id int primary key auto_increment, name nvarchar(100), name_de nvarchar(200), updated_at datetime, created_at datetime);

create table authorizationrule(id int primary key auto_increment, name nvarchar(100), name_de nvarchar(200), updated_at datetime, created_at datetime);

create table role_authorizationrule(role_id int, authorizationrule_id int, primary key(role_id, authorizationrule_id), foreign key(role_id) references role(id), foreign key (authorizationrule_id) references authorizationrule(id));

alter table user add column role_id int;

ALTER TABLE user
add constraint role_id_fk
FOREIGN KEY (role_id) REFERENCES role(id);

insert into authorizationrule(name, name_de) value
  ('rapport_read', 'Wochenrapport einsehen'),
  ('rapport_write', 'Wochenrapport schreiben'),
  ('employee_preview_read', 'Mitarbeierverzeichnis einsehen (nur Vorschau)'),
  ('employee_read', 'Mitarbeiterverzeichnis einsehen mit Details'),
  ('customer_read', 'Kundenverzeichnis einsehen'),
  ('customer_write', 'Kundenverzeichnis schreiben'),
  ('roomdispositioner_read', 'Raumplaner einsehen'),
  ('roomdispositioner_write', 'Raumplaner schreiben'),
  ('hourrecord_read', 'Sundenangaben einsehen'),
  ('hourrecord_write', 'Sundenangaben schreiben'),
  ('worker_read', 'Hofmitarbeiter einsehen'),
  ('worker_write', 'Hofmitarbeiter schreiben'),
  ('settings_read', 'Einstellungen einsehen'),
  ('settings_write', 'Einstellungen schreiben'),
  ('timerecord', 'Zeiterfassung'),
  ('timerecord_read', 'Zeiterfassung Auswertung');