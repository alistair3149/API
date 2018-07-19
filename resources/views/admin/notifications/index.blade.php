@extends('admin.layouts.default_wide')

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Notifications')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>@lang('ID')</th>
                    <th>@lang('Hash ID')</th>
                    <th>@lang('Level')</th>
                    <th>@lang('Erstellt')</th>
                    <th>@lang('Inhalt')</th>
                    <th>@lang('Ausgabedatum')</th>
                    <th>@lang('Ablaufdatum')</th>
                    <th>@lang('Ausgabe')</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>

                    @forelse($notifications as $notification)
                        <tr @if($notification->expired()) class="text-muted" @endif>
                            <td>
                                {{ $notification->id }}
                            </td>
                            <td>
                                {{ $notification->getRouteKey() }}
                            </td>
                            <td class="@unless($notification->expired()) text-{{ $notification->getBootstrapClass() }} @else text-muted @endunless">
                                {{ $notification->getLevelAsText() }}
                            </td>
                            <td title="{{ $notification->published_at->format('d.m.Y H:i:s') }}">
                                {{ $notification->published_at->format('d.m.Y') }}
                            </td>
                            <td>
                                {{ $notification->content }}
                            </td>
                            <td title="{{ $notification->published_at->format('d.m.Y H:i:s') }}">
                                {{ $notification->published_at->format('d.m.Y') }}
                            </td>
                            <td title="{{ $notification->expired_at->format('d.m.Y H:i:s') }}">
                                {{ $notification->expired_at->format('d.m.Y') }}
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if($notification->output_status)
                                        <button class="btn btn-link @if($notification->expired())text-muted @endif">
                                            @component('components.elements.icon')
                                                desktop
                                            @endcomponent
                                        </button>
                                    @endif
                                    @if($notification->output_email)
                                        <button class="btn btn-link @if($notification->expired())text-muted @endif">
                                            @component('components.elements.icon')
                                                envelope
                                            @endcomponent
                                        </button>
                                    @endif
                                    @if($notification->output_index)
                                        <button class="btn btn-link @if($notification->expired())text-muted @endif">
                                            @component('components.elements.icon')
                                                bullhorn
                                            @endcomponent
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @component('components.edit_delete_block')
                                    @slot('edit_url')
                                        {{ route('web.admin.notifications.edit', $notification->getRouteKey()) }}
                                    @endslot
                                    @if($notification->trashed())
                                        @slot('restore_url')
                                            {{ route('web.admin.notifications.edit', $notification->getRouteKey()) }}
                                        @endslot
                                    @else
                                        @slot('delete_url')
                                            {{ route('web.admin.notifications.destroy', $notification->getRouteKey()) }}
                                        @endslot
                                    @endif
                                    {{ $notification->getRouteKey() }}
                                @endcomponent
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">@lang('Keine Notifications vorhanden')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $notifications->links() }}</div>
    </div>
@endsection

@section('body__after')
    @parent
    @include('components.init_dataTables')
@endsection