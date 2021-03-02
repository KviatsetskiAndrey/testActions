#!/usr/bin/env sh
apk update && apk add --no-cache git mercurial openssh
export CGO_ENABLED=0
export GO111MODULE=on
export GOPRIVATE=github.com/Confialink
export SSH_AUTH_SOCK=/tmp/ssh_agent.sock
mkdir -p ~/.ssh && umask 0077 && echo "${REPOSITORY_PRIVATE_KEY}" > ~/.ssh/id_rsa \
&& git config --global url."git@github.com:Confialink".insteadOf https://github.com/Confialink \
&& ssh-keyscan -t rsa github.com >> ~/.ssh/known_hosts \
&& echo -e "Host github.com\n\tStrictHostKeyChecking no\n" >> ~/.ssh/config \
&& cat ~/.ssh/id_rsa \
&& cat ~/.ssh/known_hosts \
&& cat ~/.ssh/config\
&& ls -al ~/.ssh/ \
&& ssh-agent -a $SSH_AUTH_SOCK > /dev/null \
&& ssh-add ~/.ssh/id_rsa