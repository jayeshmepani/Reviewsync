
Enum "local_reviews_star_rating_enum" {
  "ONE"
  "TWO"
  "THREE"
}

Enum "reviews_star_rating_enum" {
  "ONE"
  "TWO"
  "THREE"
  "FOUR"
  "FIVE"
}

Table "ai_replies" {
  "id" bigint(20) [pk, not null, increment]
  "uuid" char(36) [not null]
  "review_id" bigint(20) [not null]
  "user_id" bigint(20) [not null]
  "reply_text" text [not null]
  "input_tokens" int(11) [default: NULL]
  "output_tokens" int(11) [default: NULL]
  "total_tokens" int(11) [default: NULL]
  "model_used" varchar(255) [default: NULL]
  "created_at" timestamp [default: NULL]
  "updated_at" timestamp [default: NULL]
  "deleted_at" timestamp [default: NULL]

  Indexes {
    uuid [unique, name: "ai_replies_uuid_unique"]
    review_id [name: "ai_replies_review_id_index"]
    user_id [name: "ai_replies_user_id_index"]
    created_at [name: "ai_replies_created_at_index"]
  }
}

Table "local_reviews" {
  "id" bigint(20) [pk, not null, increment]
  "user_id" bigint(20) [not null]
  "review_id" varchar(255) [not null]
  "reviewer_name" varchar(255) [not null]
  "star_rating" local_reviews_star_rating_enum [not null]
  "comment" text [default: NULL]
  "create_time" timestamp [not null, default: `current_timestamp()`]
  "location_id" bigint(20) [not null]
  "created_at" timestamp [default: NULL]
  "updated_at" timestamp [default: NULL]

  Indexes {
    review_id [unique, name: "local_reviews_review_id_unique"]
    location_id [name: "local_reviews_location_id_foreign"]
  }
}

Table "locations" {
  "id" bigint(20) [pk, not null, increment]
  "uuid" char(36) [not null]
  "store_code" varchar(255) [default: NULL]
  "name" varchar(255) [default: NULL]
  "title" varchar(255) [default: NULL]
  "website_uri" varchar(255) [default: NULL]
  "primary_phone" varchar(255) [default: NULL]
  "primary_category" varchar(255) [default: NULL]
  "address_lines" varchar(255) [default: NULL]
  "locality" varchar(255) [default: NULL]
  "region" varchar(255) [default: NULL]
  "postal_code" varchar(255) [default: NULL]
  "country_code" varchar(2) [default: NULL]
  "latitude" decimal(10,7) [default: NULL]
  "longitude" decimal(10,7) [default: NULL]
  "status" varchar(255) [default: NULL]
  "description" text [default: NULL]
  "place_id" varchar(255) [default: NULL]
  "maps_uri" text [default: NULL]
  "new_review_uri" text [default: NULL]
  "formatted_address" text [default: NULL]
  "user_id" bigint(20) [not null]
  "created_at" timestamp [default: NULL]
  "updated_at" timestamp [default: NULL]
  "is_visible" tinyint(1) [not null, default: 1]

  Indexes {
    uuid [unique, name: "locations_uuid_unique"]
    user_id [name: "locations_user_id_foreign"]
  }
}

Table "reviews" {
  "id" bigint(20) [pk, not null, increment]
  "user_id" bigint(20) [not null]
  "review_id" varchar(255) [not null]
  "reviewer_name" varchar(255) [not null]
  "profile_photo_url" varchar(255) [default: NULL]
  "star_rating" reviews_star_rating_enum [not null]
  "comment" text [default: NULL]
  "create_time" timestamp [not null, default: `current_timestamp()`]
  "update_time" timestamp [default: NULL]
  "reply_comment" text [default: NULL]
  "reply_update_time" timestamp [default: NULL]
  "review_name" varchar(255) [not null]
  "location_id" bigint(20) [not null]
  "created_at" timestamp [default: NULL]
  "updated_at" timestamp [default: NULL]

  Indexes {
    review_id [unique, name: "reviews_review_id_unique"]
    review_id [name: "reviews_review_id_index"]
    user_id [name: "reviews_user_id_index"]
    location_id [name: "reviews_location_id_index"]
  }
}


Table "users" {
  "id" bigint(20) [pk, not null, increment]
  "uuid" char(36) [default: NULL]
  "name" varchar(255) [not null]
  "first_name" varchar(255) [default: NULL]
  "last_name" varchar(255) [default: NULL]
  "email" varchar(255) [not null]
  "password" varchar(255) [not null]
  "profile_picture" varchar(255) [default: NULL]
  "google_id" varchar(255) [default: NULL]
  "google_token" text [default: NULL]
  "email_verified" tinyint(1) [not null, default: 0]
  "created_at" timestamp [default: NULL]
  "updated_at" timestamp [default: NULL]
  "deleted_at" timestamp [default: NULL]
  "role" varchar(255) [not null, default: 'user']

  Indexes {
    email [unique, name: "users_email_unique"]
    uuid [unique, name: "users_uuid_unique"]
  }
}

Ref "ai_replies_review_id_foreign":"reviews"."id" < "ai_replies"."review_id" [delete: cascade]

Ref "ai_replies_user_id_foreign":"users"."id" < "ai_replies"."user_id" [delete: cascade]

Ref "local_reviews_location_id_foreign":"locations"."id" < "local_reviews"."location_id" [delete: cascade]

Ref "locations_user_id_foreign":"users"."id" < "locations"."user_id" [delete: cascade]

Ref "reviews_location_id_foreign":"locations"."id" < "reviews"."location_id" [delete: cascade]

Ref "reviews_user_id_foreign":"users"."id" < "reviews"."user_id" [delete: cascade]
