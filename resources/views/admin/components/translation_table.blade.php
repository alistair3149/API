<table class="table table-striped mb-0">
    <thead>
        <tr>
            @can('web.admin.internals.view')
                <th>@lang('ID')</th>
            @endcan
            @foreach($languages as $language)
                <th>@lang($language->locale_code)</th>
            @endforeach
            @can('web.admin.starcitizen.translations.update')
                <th>&nbsp;</th>
            @endcan
        </tr>
    </thead>
    <tbody>

    @forelse($translations as $translation)
        <tr>
            @can('web.admin.internals.view')
                <td>
                    {{ $translation->getRouteKey() }}
                </td>
            @endcan
            @foreach($translation->translationsCollection() as $translationObject)
                <td>
                    {{ optional($translationObject)->translation ?? '-' }}
                </td>
            @endforeach
            @can('web.admin.starcitizen.translations.update')
                <td>
                    @component('components.edit_delete_block')
                        @slot('edit_url')
                            {{ route($editRoute, $translation->getRouteKey()) }}
                        @endslot
                        {{ $translation->getRouteKey() }}
                    @endcomponent
                </td>
            @endcan
        </tr>
    @empty
        <tr>
            <td colspan="3">@lang('Keine Übersetzungen vorhanden')</td>
        </tr>
    @endforelse
    </tbody>
</table>