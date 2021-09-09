vcl 4.0;

include "fos_ban.vcl";

backend default {
    .host = "nginx";
    .port = "80";
}

acl invalidators {
    "127.0.0.1";
    # Add any other IP addresses that your application runs on and that you
    # want to allow invalidation requests from. For instance:
    # "192.168.1.0"/24;
}

sub vcl_recv {
    call fos_ban_recv;
}

sub vcl_backend_response {
    call fos_ban_backend_response;
}

sub vcl_deliver {
    call fos_ban_deliver;
}