@extends('master')

@section('title', 'Memory Game')

@section('content')

<router-view></router-view>

@endsection
@section('pagescript')
<script src="/js/app.js"></script>
@stop
