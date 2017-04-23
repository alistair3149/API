@extends('layouts.admin')
@section('title')
    @lang('admin/users/index.header')
@endsection

@section('content')
    <table class="table table-striped" id="userTable" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th><span>@lang('admin/users/index.id')</span></th>
                <th><span>@lang('admin/users/index.name')</span></th>
                <th><span>@lang('admin/users/index.created')</span></th>
                <th><span>@lang('admin/users/index.last_login')</span></th>
                <th><span>@lang('admin/users/index.last_request')</span></th>
                <th class="text-center"><span>@lang('admin/users/index.state')</span></th>
                <th><span>@lang('admin/users/index.email')</span></th>
                <th><span>@lang('admin/users/index.api_key')</span></th>
                <th><span>@lang('admin/users/index.notes')</span></th>
                <th><span>@lang('admin/users/index.requests_per_minute')</span></th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        @if(count($users) > 0)
            @foreach($users as $user)
                <tr>
                    <td>
                        {{ $user->id }}
                    </td>
                    <td>
                        {{ $user->name }}
                    </td>
                    <td>
                        {{ Carbon\Carbon::parse($user->created_at)->format('d.m.Y') }}
                    </td>
                    <td>
                        {{ Carbon\Carbon::parse($user->last_login)->format('d.m.Y') }}
                    </td>
                    <td>
                        @unless(is_null($user->api_token_last_used))
                            {{ Carbon\Carbon::parse($user->api_token_last_used)->format('d.m.Y H:i:s') }}
                        @else
                            Nie
                        @endunless
                    </td>
                    <td class="text-center">
                        @if($user->deleted_at)
                            <span class="badge badge-info">
                                @lang('admin/users/index.deleted')
                            </span>
                        @elseif($user->isWhitelisted())
                            <span class="badge badge-success">
                                @lang('admin/users/index.whitelisted')
                            </span>
                        @elseif($user->isBlacklisted())
                            <span class="badge badge-danger">
                                @lang('admin/users/index.blacklisted')
                            </span>
                        @else
                            <span class="badge badge-default">
                                @lang('admin/users/index.normal')
                            </span>
                        @endif
                    </td>
                    <td>
                        {{ $user->email }}
                    </td>
                    <td>
                        <i class="fa fa-key" data-placement="top" data-toggle="popover" title="Key" data-content="{{ $user->api_token }}" tabindex="0"></i>
                    </td>
                    <td>
                        <i class="fa fa-book" data-placement="top" data-toggle="popover" title="Notizen" data-content="{{ $user->notes }}" data-trigger="focus" tabindex="1"></i>
                    </td>
                    <td>
                        <code>
                        @if($user->isWhitelisted() || $user->isBlacklisted())
                            -
                        @else
                            {{ $user->requests_per_minute }}
                        @endif
                        </code>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group" aria-label="">
                            <a href="{{ route('admin_users_edit_form', $user->id) }}" class="btn btn-warning">
                                <i class="fa fa-pencil"></i>
                            </a>
                            @unless($user->trashed())
                            <a href="#" class="btn btn-danger"
                                onclick="event.preventDefault();
                                document.getElementById('delete-form{{ $user->id }}').submit();">
                                <form id="delete-form{{ $user->id }}" action="{{ route('admin_users_delete') }}" method="POST" style="display: none;">
                                    <input name="_method" type="hidden" value="DELETE">
                                    <input name="id" type="hidden" value="{{ $user->id }}">
                                    {{ csrf_field() }}
                                </form>
                                <i class="fa fa-trash-o"></i>
                            </a>
                            @else
                                <a href="#" class="btn btn-success"
                                   onclick="event.preventDefault();
                                           document.getElementById('restore-form{{ $user->id }}').submit();">
                                    <form id="restore-form{{ $user->id }}" action="{{ route('admin_users_restore') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="id" value="{{ $user->id }}">
                                    </form>
                                    <i class="fa fa-repeat"></i>
                                </a>
                            @endunless
                        </div>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="11">@lang('admin/users/index.no_users_found')</td>
            </tr>
        @endif
        </tbody>
    </table>
@endsection

@section('scripts')
    <script>
        $(function () {
            $('[data-toggle="popover"]').popover()
        });
    </script>
    @include('components.init_dataTables')
@endsection
