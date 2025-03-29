<?php 

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Добро пожаловать в личный кабинет, {{ auth()->user()->name }}</h1>
    </div>
@endsection