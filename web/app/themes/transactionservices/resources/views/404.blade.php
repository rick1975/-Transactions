@extends('layouts.app')

@section('content')
  @include('partials.page-header')

  @if (! have_posts())
    <div class="mb-2">
      {!! __('Sorry, de pagina die je zoekt bestaat helaas niet.', 'sage') !!}
    </div>

    {!! get_search_form(false) !!}
  @endif
@endsection
