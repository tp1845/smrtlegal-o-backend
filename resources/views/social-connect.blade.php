<script>
    var user = JSON.parse('{!! $user !!}');
    var token = '{{ $token }}';

    window.opener.postMessage(JSON.stringify({
        token,
        user
    }), "*");

</script>
