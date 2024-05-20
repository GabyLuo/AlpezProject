CREATE SEQUENCE IF NOT EXISTS public.sys_supercluster_id_seq
    INCREMENT 1
    START 1
    MINVALUE 1;

CREATE TABLE IF NOT EXISTS public.sys_supercluster
(
    id bigint NOT NULL DEFAULT nextval('sys_supercluster_id_seq'::regclass),
    created timestamp without time zone,
    created_by bigint,
    updated timestamp without time zone,
    updated_by bigint,
    code character varying(5) COLLATE pg_catalog."default",
    name character varying(50) COLLATE pg_catalog."default",
    CONSTRAINT sys_supercluster_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS public.wms_branch_offices
    ADD COLUMN IF NOT EXISTS cluster_id bigint;
ALTER TABLE IF EXISTS public.wms_branch_offices
    ADD FOREIGN KEY (cluster_id)
    REFERENCES public.sys_supercluster (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION
    NOT VALID;

ALTER TABLE IF EXISTS public.sys_users
    ADD COLUMN IF NOT EXISTS cluster_id bigint;