alter table `bed` add `deleted_at` timestamp null
alter table `culture` add `deleted_at` timestamp null
alter table `customer` add `deleted_at` timestamp null
alter table `employee` add `deleted_at` timestamp null
alter table `hourrecords` add `deleted_at` timestamp null
alter table `hours` add `deleted_at` timestamp null
alter table `inventar` add `deleted_at` timestamp null
alter table `project` add `deleted_at` timestamp null
alter table `rapport` add `deleted_at` timestamp null
alter table `rapportdetail` add `deleted_at` timestamp null
alter table `reservation` add `deleted_at` timestamp null
alter table `room` add `deleted_at` timestamp null
alter table `timerecord` add `deleted_at` timestamp null
alter table `user` add `deleted_at` timestamp null
alter table `worktype` add `deleted_at` timestamp null



update bed set deleted_at '2019-11-27 00:00:00.000000' where isDeleted = 1;
update customer set deleted_at '2019-11-27 00:00:00.000000' where isDeleted = 1;
update employee set deleted_at '2019-11-27 00:00:00.000000' where isDeleted = 1;
update user set deleted_at '2019-11-27 00:00:00.000000' where isDeleted = 1;
update project set deleted_at '2019-11-27 00:00:00.000000' where isDeleted = 1;
update room set deleted_at '2019-11-27 00:00:00.000000' where isDeleted = 1;
update inventar set deleted_at '2019-11-27 00:00:00.000000' where isDeleted = 1;


alter table `bed` drop `isDeleted`
alter table `customer` drop `isDeleted`
alter table `employee` drop `isDeleted`
alter table `user` drop `isDeleted`
alter table `project` drop `isDeleted`
alter table `room` drop `isDeleted`
alter table `inventar` drop `isDeleted`