-- ----------------------------
-- Table structure for tb_embedding_file
-- ----------------------------
DROP TABLE IF EXISTS "public"."tb_embedding_file";
CREATE TABLE "public"."tb_embedding_file" (
  "id" int8 NOT NULL GENERATED ALWAYS AS IDENTITY (
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1
),
  "project_id" int8 NOT NULL,
  "status" int2 NOT NULL,
  "file_name" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "file_size" int4 NOT NULL,
  "content" text COLLATE "pg_catalog"."default" NOT NULL,
  "create_time" int8 NOT NULL,
  "update_time" int8 NOT NULL,
  "begin_training_time" int8 NOT NULL DEFAULT 0,
  "complete_training_time" int8 NOT NULL DEFAULT 0,
  "tokens" int8 NOT NULL DEFAULT 0,
  "pay_tokens" int8 NOT NULL DEFAULT 0,
  "ip" inet NOT NULL
)
;
COMMENT ON COLUMN "public"."tb_embedding_file"."project_id" IS '项目ID';
COMMENT ON COLUMN "public"."tb_embedding_file"."status" IS '状态';
COMMENT ON COLUMN "public"."tb_embedding_file"."file_name" IS '文件名';
COMMENT ON COLUMN "public"."tb_embedding_file"."file_size" IS '文件大小，单位：字节';
COMMENT ON COLUMN "public"."tb_embedding_file"."content" IS '文件内容';
COMMENT ON COLUMN "public"."tb_embedding_file"."create_time" IS '创建时间';
COMMENT ON COLUMN "public"."tb_embedding_file"."update_time" IS '更新时间';
COMMENT ON COLUMN "public"."tb_embedding_file"."begin_training_time" IS '开始训练时间';
COMMENT ON COLUMN "public"."tb_embedding_file"."complete_training_time" IS '完成训练时间';
COMMENT ON COLUMN "public"."tb_embedding_file"."tokens" IS 'Token数量';
COMMENT ON COLUMN "public"."tb_embedding_file"."pay_tokens" IS '支付 Token 数量';
COMMENT ON COLUMN "public"."tb_embedding_file"."ip" IS 'IP地址';
COMMENT ON TABLE "public"."tb_embedding_file" IS '训练的文件';

-- ----------------------------
-- Table structure for tb_embedding_project
-- ----------------------------
DROP TABLE IF EXISTS "public"."tb_embedding_project";
CREATE TABLE "public"."tb_embedding_project" (
  "id" int8 NOT NULL GENERATED ALWAYS AS IDENTITY (
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1
),
  "member_id" int4 NOT NULL,
  "name" varchar(32) COLLATE "pg_catalog"."default" NOT NULL,
  "total_file_size" int4 NOT NULL,
  "create_time" int8 NOT NULL,
  "update_time" int8 NOT NULL,
  "status" int2 NOT NULL,
  "tokens" int8 NOT NULL DEFAULT 0,
  "pay_tokens" int8 NOT NULL DEFAULT 0,
  "ip" inet NOT NULL,
  "public" bool NOT NULL DEFAULT false,
  "section_separator" varchar(16) COLLATE "pg_catalog"."default" NOT NULL DEFAULT ''::character varying,
  "section_split_length" int4 NOT NULL DEFAULT 512,
  "section_split_by_title" bool NOT NULL DEFAULT true,
  "chat_config" json NOT NULL DEFAULT '{}'::json,
  "similarity" float4 NOT NULL DEFAULT 0,
  "top_sections" int2 NOT NULL DEFAULT 0,
  "prompt" text COLLATE "pg_catalog"."default" NOT NULL DEFAULT ''::text,
  "embedding_model" varchar(255) COLLATE "pg_catalog"."default" NOT NULL DEFAULT 'text-embedding-ada-002'::character varying
)
;
COMMENT ON COLUMN "public"."tb_embedding_project"."member_id" IS '用户ID';
COMMENT ON COLUMN "public"."tb_embedding_project"."name" IS '项目名称';
COMMENT ON COLUMN "public"."tb_embedding_project"."total_file_size" IS '文件总大小，单位：字节';
COMMENT ON COLUMN "public"."tb_embedding_project"."create_time" IS '创建时间';
COMMENT ON COLUMN "public"."tb_embedding_project"."update_time" IS '更新时间';
COMMENT ON COLUMN "public"."tb_embedding_project"."status" IS '状态';
COMMENT ON COLUMN "public"."tb_embedding_project"."tokens" IS 'Token数量';
COMMENT ON COLUMN "public"."tb_embedding_project"."pay_tokens" IS '支付 Token 数量';
COMMENT ON COLUMN "public"."tb_embedding_project"."ip" IS 'IP地址';
COMMENT ON COLUMN "public"."tb_embedding_project"."public" IS '是否公开使用';
COMMENT ON COLUMN "public"."tb_embedding_project"."section_separator" IS '段落分隔符';
COMMENT ON COLUMN "public"."tb_embedding_project"."section_split_length" IS '段落分割长度';
COMMENT ON COLUMN "public"."tb_embedding_project"."section_split_by_title" IS '使用标题分割段落';
COMMENT ON COLUMN "public"."tb_embedding_project"."chat_config" IS '聊天对话推荐配置';
COMMENT ON COLUMN "public"."tb_embedding_project"."similarity" IS '相似度';
COMMENT ON COLUMN "public"."tb_embedding_project"."top_sections" IS '使用最匹配的段落数量';
COMMENT ON COLUMN "public"."tb_embedding_project"."prompt" IS '提示语';
COMMENT ON COLUMN "public"."tb_embedding_project"."embedding_model" IS '嵌入式模型名称';
COMMENT ON TABLE "public"."tb_embedding_project" IS '文件训练项目';

-- ----------------------------
-- Table structure for tb_embedding_public_project
-- ----------------------------
DROP TABLE IF EXISTS "public"."tb_embedding_public_project";
CREATE TABLE "public"."tb_embedding_public_project" (
  "project_id" int8 NOT NULL,
  "status" int2 NOT NULL DEFAULT 0,
  "time" int8 NOT NULL DEFAULT 0,
  "index" int2 NOT NULL
)
;
COMMENT ON COLUMN "public"."tb_embedding_public_project"."project_id" IS '项目ID';
COMMENT ON COLUMN "public"."tb_embedding_public_project"."status" IS '状态';
COMMENT ON COLUMN "public"."tb_embedding_public_project"."time" IS '时间';
COMMENT ON COLUMN "public"."tb_embedding_public_project"."index" IS '排序，越小越靠前';
COMMENT ON TABLE "public"."tb_embedding_public_project" IS '公共项目';

-- ----------------------------
-- Table structure for tb_embedding_qa
-- ----------------------------
DROP TABLE IF EXISTS "public"."tb_embedding_qa";
CREATE TABLE "public"."tb_embedding_qa" (
  "id" int8 NOT NULL GENERATED ALWAYS AS IDENTITY (
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1
),
  "member_id" int4 NOT NULL,
  "project_id" int8 NOT NULL,
  "question" text COLLATE "pg_catalog"."default" NOT NULL,
  "answer" text COLLATE "pg_catalog"."default" NOT NULL DEFAULT ''::text,
  "begin_time" int8 NOT NULL DEFAULT 0,
  "complete_time" int8 NOT NULL DEFAULT 0,
  "tokens" int4 NOT NULL DEFAULT 0,
  "config" json NOT NULL,
  "status" int2 NOT NULL,
  "title" varchar(16) COLLATE "pg_catalog"."default" NOT NULL,
  "create_time" int8 NOT NULL,
  "pay_tokens" int4 NOT NULL DEFAULT 0,
  "ip" inet NOT NULL,
  "similarity" float4 NOT NULL DEFAULT 0,
  "top_sections" int2 NOT NULL DEFAULT 0,
  "prompt" text COLLATE "pg_catalog"."default" NOT NULL DEFAULT ''::text
)
;
COMMENT ON COLUMN "public"."tb_embedding_qa"."member_id" IS '用户ID';
COMMENT ON COLUMN "public"."tb_embedding_qa"."project_id" IS '项目ID';
COMMENT ON COLUMN "public"."tb_embedding_qa"."question" IS '问题';
COMMENT ON COLUMN "public"."tb_embedding_qa"."answer" IS '回答';
COMMENT ON COLUMN "public"."tb_embedding_qa"."begin_time" IS '开始时间';
COMMENT ON COLUMN "public"."tb_embedding_qa"."complete_time" IS '结束时间';
COMMENT ON COLUMN "public"."tb_embedding_qa"."tokens" IS '总tokens';
COMMENT ON COLUMN "public"."tb_embedding_qa"."config" IS '配置';
COMMENT ON COLUMN "public"."tb_embedding_qa"."status" IS '状态';
COMMENT ON COLUMN "public"."tb_embedding_qa"."title" IS '标题';
COMMENT ON COLUMN "public"."tb_embedding_qa"."create_time" IS '创建时间';
COMMENT ON COLUMN "public"."tb_embedding_qa"."pay_tokens" IS '支付 Token 数量';
COMMENT ON COLUMN "public"."tb_embedding_qa"."ip" IS 'IP地址';
COMMENT ON COLUMN "public"."tb_embedding_qa"."similarity" IS '相似度';
COMMENT ON COLUMN "public"."tb_embedding_qa"."top_sections" IS '使用最匹配的段落数量';
COMMENT ON COLUMN "public"."tb_embedding_qa"."prompt" IS '提示语';
COMMENT ON TABLE "public"."tb_embedding_qa" IS '训练文件问答';

-- ----------------------------
-- Table structure for tb_embedding_section
-- ----------------------------
DROP TABLE IF EXISTS "public"."tb_embedding_section";
CREATE TABLE "public"."tb_embedding_section" (
  "id" int8 NOT NULL GENERATED ALWAYS AS IDENTITY (
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1
),
  "project_id" int8 NOT NULL,
  "file_id" int8 NOT NULL,
  "status" int2 NOT NULL,
  "content" text COLLATE "pg_catalog"."default" NOT NULL,
  "vector" "public"."vector" NOT NULL,
  "create_time" int8 NOT NULL,
  "update_time" int8 NOT NULL,
  "begin_training_time" int8 NOT NULL DEFAULT 0,
  "complete_training_time" int8 NOT NULL DEFAULT 0,
  "reason" text COLLATE "pg_catalog"."default" NOT NULL DEFAULT ''::text,
  "tokens" int4 NOT NULL,
  "pay_tokens" int4 NOT NULL DEFAULT 0,
  "title" text COLLATE "pg_catalog"."default" NOT NULL DEFAULT ''::text
)
;
COMMENT ON COLUMN "public"."tb_embedding_section"."project_id" IS '项目ID';
COMMENT ON COLUMN "public"."tb_embedding_section"."file_id" IS '文件ID';
COMMENT ON COLUMN "public"."tb_embedding_section"."status" IS '状态';
COMMENT ON COLUMN "public"."tb_embedding_section"."content" IS '文件内容';
COMMENT ON COLUMN "public"."tb_embedding_section"."vector" IS '向量';
COMMENT ON COLUMN "public"."tb_embedding_section"."create_time" IS '创建时间';
COMMENT ON COLUMN "public"."tb_embedding_section"."update_time" IS '更新时间';
COMMENT ON COLUMN "public"."tb_embedding_section"."begin_training_time" IS '开始训练时间';
COMMENT ON COLUMN "public"."tb_embedding_section"."complete_training_time" IS '完成训练时间';
COMMENT ON COLUMN "public"."tb_embedding_section"."reason" IS '失败原因';
COMMENT ON COLUMN "public"."tb_embedding_section"."tokens" IS 'Token数量';
COMMENT ON COLUMN "public"."tb_embedding_section"."pay_tokens" IS '支付 Token 数量';
COMMENT ON COLUMN "public"."tb_embedding_section"."title" IS '标题';
COMMENT ON TABLE "public"."tb_embedding_section" IS '训练内容段落';

-- ----------------------------
-- Indexes structure for table tb_embedding_file
-- ----------------------------
CREATE INDEX "tb_embedding_file_project_id_update_time_idx" ON "public"."tb_embedding_file" USING btree (
  "project_id" "pg_catalog"."int8_ops" ASC NULLS LAST,
  "update_time" "pg_catalog"."int8_ops" DESC NULLS LAST
);
CREATE INDEX "tb_embedding_file_status_idx" ON "public"."tb_embedding_file" USING btree (
  "status" "pg_catalog"."int2_ops" ASC NULLS LAST,
  "update_time" "pg_catalog"."int8_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table tb_embedding_file
-- ----------------------------
ALTER TABLE "public"."tb_embedding_file" ADD CONSTRAINT "tb_embedding_file_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Indexes structure for table tb_embedding_project
-- ----------------------------
CREATE INDEX "tb_embedding_project_member_id_idx" ON "public"."tb_embedding_project" USING btree (
  "member_id" "pg_catalog"."int4_ops" ASC NULLS LAST,
  "update_time" "pg_catalog"."int8_ops" DESC NULLS FIRST
);
CREATE INDEX "tb_embedding_project_status_idx" ON "public"."tb_embedding_project" USING btree (
  "status" "pg_catalog"."int2_ops" ASC NULLS LAST,
  "update_time" "pg_catalog"."int8_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table tb_embedding_project
-- ----------------------------
ALTER TABLE "public"."tb_embedding_project" ADD CONSTRAINT "tb_embedding_project_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Indexes structure for table tb_embedding_public_project
-- ----------------------------
CREATE INDEX "tb_embedding_public_project_status_index_time_idx" ON "public"."tb_embedding_public_project" USING btree (
  "status" "pg_catalog"."int2_ops" ASC NULLS LAST,
  "index" "pg_catalog"."int2_ops" ASC NULLS LAST,
  "time" "pg_catalog"."int8_ops" DESC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table tb_embedding_public_project
-- ----------------------------
ALTER TABLE "public"."tb_embedding_public_project" ADD CONSTRAINT "tb_embedding_public_project_pkey" PRIMARY KEY ("project_id");

-- ----------------------------
-- Indexes structure for table tb_embedding_qa
-- ----------------------------
CREATE INDEX "tb_embedding_qa_member_id_idx" ON "public"."tb_embedding_qa" USING btree (
  "member_id" "pg_catalog"."int4_ops" ASC NULLS LAST
);
CREATE INDEX "tb_embedding_qa_project_id_idx" ON "public"."tb_embedding_qa" USING btree (
  "project_id" "pg_catalog"."int8_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table tb_embedding_qa
-- ----------------------------
ALTER TABLE "public"."tb_embedding_qa" ADD CONSTRAINT "tb_embedding_qa_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Indexes structure for table tb_embedding_section
-- ----------------------------
CREATE INDEX "tb_embedding_section_project_id_file_id_idx" ON "public"."tb_embedding_section" USING btree (
  "project_id" "pg_catalog"."int8_ops" ASC NULLS LAST,
  "file_id" "pg_catalog"."int8_ops" ASC NULLS LAST
);
CREATE INDEX "tb_embedding_section_status_idx" ON "public"."tb_embedding_section" USING btree (
  "status" "pg_catalog"."int2_ops" ASC NULLS LAST,
  "update_time" "pg_catalog"."int8_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table tb_embedding_section
-- ----------------------------
ALTER TABLE "public"."tb_embedding_section" ADD CONSTRAINT "tb_embedding_section_pkey" PRIMARY KEY ("id");
