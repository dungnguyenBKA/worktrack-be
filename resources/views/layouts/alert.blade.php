@if (session('message'))
<div class="my-2 alert @if (session('success')) alert-success @elseif(session('warning')) alert-warning @else alert-danger @endif">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <strong>{{ session('message') }}</strong>
</div>
@endif
