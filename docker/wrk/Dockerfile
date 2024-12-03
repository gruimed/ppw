FROM debian:bookworm-slim AS builder

RUN apt update && apt install -y git make gcc libssl-dev libz-dev

RUN cd /
RUN git clone --branch main --single-branch https://github.com/devhands-io/wrkx.git
WORKDIR /wrkx
RUN make clean && make

FROM debian:bookworm AS bin

RUN apt-get update && apt-get install -y openssl netbase && apt-get clean \
    && rm -rf /var/cache/debconf/* && rm -rf /var/lib/apt/* && rm -rf /var/lib/dpkg/*

COPY --from=builder /wrkx/wrk /usr/local/bin/wrk
RUN chmod +x /usr/local/bin/wrk

WORKDIR /opt/data