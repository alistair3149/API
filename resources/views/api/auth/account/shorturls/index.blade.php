@extends('api.auth.layouts.default_wide')

{{-- Page Title --}}
@section('title', trans('auth/account/shorturls/index.header'))


@section('content')
    <div class="row">
        <div class="col-12 col-md-8 col-lg-3 mx-auto">
        @if (session('hash_name'))
                <div class="alert alert-success text-center">
                    {{config('app.shorturl_url')}}/{{ session('hash_name') }}
                </div>
            @endif
            @include('components.errors')
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-xl-11 mx-auto mt-xl-1">
            <div class="card">
                <h4 class="card-header">ShortURLs</h4>

                <div class="card-body px-0">
                    <table class="table table-striped table-responsive" id="urlTable" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th><span>Short</span></th>
                            <th><span>@lang('auth/account/shorturls/index.url')</span></th>
                            <th><span>@lang('auth/account/shorturls/index.hash')</span></th>
                            <th><span>@lang('auth/account/shorturls/index.created_at')</span></th>
                            <th><span>@lang('auth/account/shorturls/index.expires')</span></th>
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($urls) > 0)
                            @foreach($urls as $url)
                                <tr>
                                    <td>
                                        {{config('app.shorturl_url')}}/{{ $url->hash_name }}
                                    </td>
                                    <td>
                                        {{ $url->url }}
                                    </td>
                                    <td>
                                        {{ $url->hash_name }}
                                    </td>
                                    <td>
                                        {{ Carbon\Carbon::parse($url->created_at)->format('d.m.Y') }}
                                    </td>
                                    <td>
                                        @component('components.shorturls.expiresfield', ['expires' => $url->expires])@endcomponent
                                    </td>
                                    <td>
                                        @component('components.edit_delete_block')
                                            @slot('edit_url')
                                                {{ route('account_urls_edit_form', $url->id) }}
                                            @endslot
                                            @slot('delete_url')
                                                {{ route('account_urls_delete') }}
                                            @endslot
                                            {{ $url->id }}
                                        @endcomponent
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7">@lang('auth/account/shorturls/index.no_urls_found')</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>


            </div>
        </div>
    </div>
@endsection


@section('body__after')
    @parent
    @include('components.init_dataTables')
@endsection
