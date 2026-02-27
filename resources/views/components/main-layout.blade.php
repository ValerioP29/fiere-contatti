@props(['title' => null])

@php($title = $title)

@extends('layouts.main')

@section('content')
@endsection

{{ $slot }}