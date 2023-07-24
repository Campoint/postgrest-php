ARG POSTGRES_VERSION

FROM postgres:${POSTGRES_VERSION}-alpine

RUN apk add --no-cache curl make && \
    curl -sSL -o /pgjwt.zip "https://github.com/michelp/pgjwt/archive/f3d82fd30151e754e19ce5d6a06c71c20689ce3d.zip" && \
    mkdir /pgjwt && \
    unzip /pgjwt.zip -d /pgjwt && \
    cd /pgjwt/pgjwt-f3d82fd30151e754e19ce5d6a06c71c20689ce3d && \
    make && make install && \
    rm -rf /pgjwt /pgjwt.zip && \
    apk del curl make
