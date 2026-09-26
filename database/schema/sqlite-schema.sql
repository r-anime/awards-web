CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "applications"(
  "id" integer primary key autoincrement not null,
  "year" integer not null,
  "start_time" datetime not null,
  "end_time" datetime not null,
  "form" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "app_questions"(
  "id" integer primary key autoincrement not null,
  "app_id" integer not null,
  "question" text not null,
  "type" varchar not null
);
CREATE TABLE IF NOT EXISTS "categories"(
  "id" integer primary key autoincrement not null,
  "year" integer not null,
  "name" varchar not null,
  "type" varchar not null,
  "order" integer,
  "entry_type" varchar not null
);
CREATE TABLE IF NOT EXISTS "category_infos"(
  "category_id" integer not null,
  "description" text not null,
  "sotc_blurb" text
);
CREATE TABLE IF NOT EXISTS "category_eligibles"(
  "id" integer primary key autoincrement not null,
  "category_id" integer not null,
  "entry_id" integer not null,
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "category_nominees"(
  "id" integer primary key autoincrement not null,
  "category_id" integer not null,
  "entry_id" integer not null,
  "active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "nominee_votes"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "category_id" integer not null,
  "cat_entry_id" integer not null,
  "entry_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE TABLE IF NOT EXISTS "final_votes"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "category_id" integer not null,
  "cat_nom_id" integer not null,
  "entry_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE TABLE IF NOT EXISTS "options"(
  "id" integer primary key autoincrement not null,
  "option" varchar not null,
  "value" varchar not null
);
CREATE TABLE IF NOT EXISTS "watch_stats"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "anime_id" integer not null,
  "active" tinyint(1) not null default '0'
);
CREATE TABLE IF NOT EXISTS "results"(
  "id" integer primary key autoincrement not null,
  "year" integer not null,
  "category_id" integer not null,
  "name" varchar not null,
  "image" text not null,
  "entry_id" integer not null,
  "jury_rank" integer not null,
  "public_rank" integer not null,
  "description" text not null,
  "staff_credits" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "pages"(
  "id" integer primary key autoincrement not null,
  "type" varchar not null,
  "author_id" integer not null,
  "slug" varchar not null,
  "feature_image" text,
  "content" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "acknowledgements"(
  "id" integer primary key autoincrement not null,
  "title" text not null,
  "subtitle" text not null,
  "content" text not null,
  "year" integer not null,
  "order" integer not null
);
CREATE TABLE IF NOT EXISTS "logs"(
  "id" integer primary key autoincrement not null,
  "type" varchar not null,
  "description" text not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "reddit_banlists"(
  "id" integer primary key autoincrement not null,
  "reddit_user" varchar not null,
  "banned" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "post_author"(
  "page_id" integer not null,
  "user_id" integer not null
);
CREATE TABLE IF NOT EXISTS "user_public_ids"(
  "user_id" integer not null,
  "uuid" varchar not null
);
CREATE TABLE IF NOT EXISTS "item_names"(
  "entry_id" integer not null,
  "language_code" varchar not null,
  "name" varchar not null
);
CREATE TABLE IF NOT EXISTS "watch_stats_calculated"(
  "category_id" integer not null,
  "anime_id" integer not null,
  "votes" integer not null,
  "watched" double not null,
  "supported" double not null
);
CREATE TABLE IF NOT EXISTS "ip_bans"(
  "id" integer primary key autoincrement not null,
  "hash" varchar not null,
  "banned" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "entries"(
  "id" integer primary key autoincrement not null,
  "type" varchar not null,
  "name" varchar not null,
  "year" integer not null,
  "theme_version" varchar,
  "image" varchar,
  "link" varchar,
  "parent_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "anilist_id" integer
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar,
  "email" varchar,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "reddit_user" varchar,
  "role" integer not null default('0'),
  "flags" integer not null default('0'),
  "avatar" text,
  "about" text,
  "uuid" varchar not null
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE UNIQUE INDEX "users_uuid_unique" on "users"("uuid");
CREATE TABLE IF NOT EXISTS "app_scores"(
  "id" integer primary key autoincrement not null,
  "applicant_id" integer not null,
  "scorer_id" integer not null,
  "score" integer not null,
  "comment" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "question_uuid" varchar,
  "question_id" varchar not null
);
CREATE INDEX "app_scores_question_uuid_index" on "app_scores"("question_uuid");
CREATE INDEX "entries_anilist_id_index" on "entries"("anilist_id");
CREATE TABLE IF NOT EXISTS "feedback"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "message" text not null,
  "created_at" datetime,
  "updated_at" datetime,
  "ip_hash" varchar not null,
  "validated" tinyint(1) not null
);
CREATE INDEX "feedback_ip_hash_index" on "feedback"("ip_hash");
CREATE TABLE IF NOT EXISTS "app_answers"(
  "id" integer primary key autoincrement not null,
  "question_id" varchar not null,
  "applicant_id" integer not null,
  "answer" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "socialite_users"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "provider" varchar not null,
  "provider_id" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade on update cascade
);
CREATE UNIQUE INDEX "socialite_users_provider_provider_id_unique" on "socialite_users"(
  "provider",
  "provider_id"
);
CREATE INDEX "socialite_users_user_id_index" on "socialite_users"("user_id");
CREATE TABLE IF NOT EXISTS "honorable_mentions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "year" integer not null,
  "category_id" integer not null,
  "writeup" text not null,
  "created_at" datetime,
  "updated_at" datetime
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2024_07_28_000534_modify_users_table',1);
INSERT INTO migrations VALUES(5,'2024_07_28_005316_create_applications_table',1);
INSERT INTO migrations VALUES(6,'2024_07_28_005337_create_app_questions_table',1);
INSERT INTO migrations VALUES(7,'2024_07_28_005348_create_app_scores_table',1);
INSERT INTO migrations VALUES(8,'2024_07_28_005404_create_app_answers_table',1);
INSERT INTO migrations VALUES(9,'2024_07_28_005444_create_categories_table',1);
INSERT INTO migrations VALUES(10,'2024_07_28_005454_create_category_infos_table',1);
INSERT INTO migrations VALUES(11,'2024_07_28_005506_create_category_eligibles_table',1);
INSERT INTO migrations VALUES(12,'2024_07_28_005514_create_category_nominees_table',1);
INSERT INTO migrations VALUES(13,'2024_07_28_005532_create_nominee_votes_table',1);
INSERT INTO migrations VALUES(14,'2024_07_28_005542_create_final_votes_table',1);
INSERT INTO migrations VALUES(15,'2024_07_28_005658_create_options_table',1);
INSERT INTO migrations VALUES(16,'2024_07_28_005706_create_watch_stats_table',1);
INSERT INTO migrations VALUES(17,'2024_07_28_005715_create_results_table',1);
INSERT INTO migrations VALUES(18,'2024_07_28_005835_create_pages_table',1);
INSERT INTO migrations VALUES(19,'2024_07_28_005947_create_acknowledgements_table',1);
INSERT INTO migrations VALUES(20,'2024_07_28_010002_create_feedback_table',1);
INSERT INTO migrations VALUES(21,'2024_07_28_010011_create_logs_table',1);
INSERT INTO migrations VALUES(22,'2024_07_28_010029_create_reddit_banlists_table',1);
INSERT INTO migrations VALUES(23,'2024_07_28_010249_create_post_author_table',1);
INSERT INTO migrations VALUES(24,'2024_07_28_010630_create_user_public_ids_table',1);
INSERT INTO migrations VALUES(25,'2024_07_28_040509_create_item_names_table',1);
INSERT INTO migrations VALUES(26,'2024_07_28_042956_create_watch_stat_calculateds_table',1);
INSERT INTO migrations VALUES(27,'2024_07_28_045743_create_ip_bans_table',1);
INSERT INTO migrations VALUES(28,'2025_05_11_030430_create_entries_table',1);
INSERT INTO migrations VALUES(29,'2025_07_21_154256_create_socialite_users_table',1);
INSERT INTO migrations VALUES(30,'2025_09_28_191558_convert_staff_credits_json_to_key_value_format',1);
INSERT INTO migrations VALUES(31,'2025_09_29_024950_add_question_uuid_to_app_scores_table',1);
INSERT INTO migrations VALUES(32,'2025_09_29_172747_make_name_field_nullable_in_users_table',1);
INSERT INTO migrations VALUES(33,'2025_09_29_173920_make_email_nullable_in_users_table',1);
INSERT INTO migrations VALUES(34,'2025_09_29_213112_add_uuid_to_users_table',1);
INSERT INTO migrations VALUES(35,'2025_09_29_220227_add_question_id_to_app_scores_table',1);
INSERT INTO migrations VALUES(36,'2025_10_03_201017_add_webhook_options_to_options_table',1);
INSERT INTO migrations VALUES(37,'2025_10_03_230612_change_question_id_to_string_in_app_answers_table',1);
INSERT INTO migrations VALUES(38,'2025_10_03_231610_change_question_id_to_string_in_app_scores_table',1);
INSERT INTO migrations VALUES(39,'2025_10_03_232809_add_anilist_id_to_entries_table',1);
INSERT INTO migrations VALUES(40,'2025_10_04_000903_add_default_uuid_to_users_table',1);
INSERT INTO migrations VALUES(41,'2025_10_04_001757_add_default_password_to_users_table',1);
INSERT INTO migrations VALUES(42,'2025_10_04_012723_add_ip_hash_to_feedback_table',1);
INSERT INTO migrations VALUES(43,'2025_10_04_202423_make_answer_nullable_in_app_answers_table',1);
INSERT INTO migrations VALUES(44,'2025_10_05_000001_add_foreign_key_to_socialite_users_table',1);
INSERT INTO migrations VALUES(45,'2025_12_11_152753_add_validated_to_feedback',1);
INSERT INTO migrations VALUES(46,'2025_12_31_093758_add_nomination_voting_date_options_to_options_table',1);
INSERT INTO migrations VALUES(47,'2026_01_08_103145_add_entry_type_to_categories',1);
INSERT INTO migrations VALUES(48,'2026_01_09_100000_add_final_voting_date_options_to_options_table',1);
INSERT INTO migrations VALUES(49,'2026_02_19_100000_add_results_display_date_option_to_options_table',1);
INSERT INTO migrations VALUES(50,'2026_02_20_221812_create_honorable_mentions_table',1);
INSERT INTO migrations VALUES(51,'2026_03_05_230058_add_year_to_acknowledgements',1);
INSERT INTO migrations VALUES(52,'2026_03_17_231806_add_order_to_acknowledgements',1);
