#!/bin/sh

[ ! -f .env ] || export $(sed 's/#.*//g' .env | xargs)
[ ! -f .envlocal ] || export $(sed 's/#.*//g' .env.local | xargs)

sleep 5

awslocal s3api create-bucket --bucket $AWS_S3_BUCKET

echo "done"
