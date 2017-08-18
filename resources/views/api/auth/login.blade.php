@extends('api.layouts.full_width')

{{-- Page Title --}}
@section('title', trans('auth/login.header'))

@section('content')
    @component('components.heading', [
        'class' => 'text-center mb-5',
        'route' => route('api_index'),
    ])@endcomponent

    @include('components.errors')

    <div class="card bg-dark text-light-grey">
        <h4 class="card-header">API @lang('auth/login.header')</h4>
        <div class="card-body">

            @component('components.forms.form', ['action' => route('auth_login')])
                @component('components.forms.form-group', [
                    'inputType' => 'email',
                    'label' => trans('auth/login.email'),
                    'id' => 'email',
                    'required' => 1,
                    'autofocus' => 1,
                    'value' => old('email'),
                    'tabIndex' => 1,
                    'inputOptions' => 'spellcheck=false',
                ])@endcomponent

                @component('components.forms.form-group', [
                    'inputType' => 'password',
                    'label' => trans('auth/login.password'),
                    'id' => 'password',
                    'required' => 1,
                    'tabIndex' => 2,
                ])@endcomponent

                <button class="btn btn-outline-secondary">
                    @lang('auth/login.login')
                </button>
                <a href="{{ route('password.request') }}" class="btn btn-link pull-right text-light-grey">
                    @lang('auth/login.forgot_password')
                </a>
            @endcomponent
        </div>
    </div>
@endsection