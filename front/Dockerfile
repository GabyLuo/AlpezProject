FROM nginx:1.19.0-alpine
ARG quasar_cli_version

RUN apk update && apk upgrade

RUN apk add --update npm nodejs

RUN npm install -g @quasar/cli@$quasar_cli_version

