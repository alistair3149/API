<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use App\Models\ShortURL\ShortURLWhitelist;
use App\Models\User;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Class AdminShortURLController
 * @package App\Http\Controllers\Auth
 */
class ShortURLController extends Controller
{
    use ProfilesMethodsTrait;

    /**
     * Returns the ShortURL List View
     *
     * @return View
     */
    public function showURLsListView() : View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.shorturls.index')->with(
            'urls',
            ShortURL::all()
        );
    }

    /**
     * Returns the ShortURL List View
     *
     * @param int $id UserID
     *
     * @return View
     */
    public function showURLsListForUserView(int $id) : View
    {
        app('Log')::info(make_name_readable(__FUNCTION__), ['id' => $id]);

        return view('admin.shorturls.index')->with(
            'urls',
            User::find($id)->shortURLs()->getResults()
        );
    }

    /**
     * Returns the ShortUrl Whitelist View
     *
     * @return View
     */
    public function showURLWhitelistView() : View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.shorturls.whitelists.index')->with(
            'urls',
            ShortURLWhitelist::all()
        );
    }

    /**
     * Returns the View to add a ShortURL Whitelist URL
     *
     * @return View
     */
    public function showAddURLWhitelistView() : View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('admin.shorturls.whitelists.add');
    }

    /**
     * Returns the View to edit a ShortURL
     *
     * @param int $id The ShortURL ID
     *
     * @return View | RedirectResponse
     */
    public function showEditURLView(int $id)
    {
        $this->startProfiling(__FUNCTION__);

        app('Log')::info(make_name_readable(__FUNCTION__), ['id' => $id]);

        try {
            $this->addTrace(__FUNCTION__, "Getting ShortURL for ID: {$id}", __LINE__);
            $url = ShortURL::findOrFail($id);
            $this->stopProfiling(__FUNCTION__);

            return view('admin.shorturls.edit')
                        ->with('url', $url)
                        ->with('users', User::all());
        } catch (ModelNotFoundException $e) {
            app('Log')::warning("URL with ID: {$id} not found");
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_list');
    }

    /**
     * Deletes a ShortURL by ID
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function deleteURL(Request $request) : RedirectResponse
    {
        $this->startProfiling(__FUNCTION__);

        $this->validate($request, [
            'id' => 'required|exists:short_urls|int',
        ]);

        $url = ShortURL::findOrFail($request->id);
        app('Log')::notice('URL deleted', [
            'deleted_by' => Auth::id(),
            'url_id' => $url->id,
            'url' => $url->url,
            'hash_name' => $url->hash_name,
        ]);
        $url->delete();

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_list');
    }

    /**
     * Deletes a ShortURL Whitelisted URL by ID
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function deleteWhitelistURL(Request $request) : RedirectResponse
    {
        $this->startProfiling(__FUNCTION__);

        $this->validate($request, [
            'id' => 'required|exists:short_url_whitelists|int',
        ]);

        $url = ShortURLWhitelist::findOrFail($request->id);
        app('Log')::notice('Whitelist URL deleted', [
            'deleted_by' => Auth::id(),
            'url_id' => $url->id,
            'url' => $url->url,
        ]);
        $url->delete();

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_whitelist_list');
    }

    /**
     * Adds a new Whitelisted URL
     *
     * @param Request $request The Add Whitelist URL Request
     *
     * @return Redirect | RedirectResponse
     */
    public function addWhitelistURL(Request $request)
    {
        $this->startProfiling(__FUNCTION__);

        $data = [
            'url' => ShortURL::sanitizeURL($request->get('url')),
            'internal' => $request->get('internal'),
        ];

        $rules = [
            'url' => 'required|active_url|max:255|unique:short_url_whitelists',
            'internal' => 'required',
        ];

        validate_array($data, $rules, $request);

        $this->addTrace(__FUNCTION__, 'Adding WhitelistURL', __LINE__);
        ShortURLWhitelist::createWhitelistURL([
            'url' => parse_url($request->get('url'))['host'],
            'internal' => !$request->get('internal')[0],
        ]);

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_whitelist_list');
    }

    /**
     * Updates a ShortURL by ID
     *
     * @param Request $request The Update Request
     *
     * @return Redirect | RedirectResponse
     */
    public function updateURL(Request $request)
    {
        $this->startProfiling(__FUNCTION__);

        $this->validate($request, [
            'id' => 'required|exists:short_urls|int',
        ]);

        $data = [
            'url' => ShortURL::sanitizeURL($request->get('url')),
            'hash_name' => $request->get('hash_name'),
            'user_id' => $request->get('user_id'),
            'expires' => $request->get('expires'),
        ];

        $rules = [
            'url' => 'required|url|max:255',
            'hash_name' => 'required|alpha_dash|max:32',
            'user_id' => 'required|integer|exists:users,id',
            'expires' => 'nullable|date',
        ];

        validate_array($data, $rules, $request);

        try {
            $this->addTrace(__FUNCTION__, "Updating ShortURL", __LINE__);
            ShortURL::updateShortURL([
                'id' => $request->id,
                'url' => ShortURL::sanitizeURL($request->get('url')),
                'hash_name' => $request->get('hash_name'),
                'user_id' => $request->get('user_id'),
                'expires' => $request->get('expires'),
            ]);
        } catch (URLNotWhitelistedException | HashNameAlreadyAssignedException $e) {
            $this->addTrace(__FUNCTION__, get_class($e), __LINE__);
            $this->stopProfiling(__FUNCTION__);

            return back()->withErrors($e->getMessage())
                         ->withInput(Input::all());
        }

        $this->stopProfiling(__FUNCTION__);

        return redirect()->route('admin_urls_list');
    }
}
