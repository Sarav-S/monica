<?php

namespace App\Http\Controllers\Api;

use App\Gift;
use Validator;
use App\Contact;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Resources\Gift\Gift as GiftResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ApiGiftController extends ApiController
{
    /**
     * Get the list of gifts.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $gifts = auth()->user()->account->gifts()
                                ->paginate($this->getLimitPerPage());

        return GiftResource::collection($gifts);
    }

    /**
     * Get the detail of a given gift.
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            $gift = Gift::where('account_id', auth()->user()->account_id)
                ->where('id', $id)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        }

        return new GiftResource($gift);
    }

    /**
     * Store the gift.
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validates basic fields to create the entry
        $validator = Validator::make($request->all(), [
            'is_for' => 'integer|nullable',
            'name' => 'required|string|max:255',
            'comment' => 'string|max:1000000|nullable',
            'url' => 'string|max:1000000|nullable',
            'value' => 'string|max:255',
            'is_an_idea' => 'boolean',
            'has_been_offered' => 'boolean',
            'date_offered' => 'date|nullable',
            'contact_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->setErrorCode(32)
                        ->respondWithError($validator->errors()->all());
        }

        try {
            $contact = Contact::where('account_id', auth()->user()->account_id)
                ->where('id', $request->input('contact_id'))
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        }

        if (! is_null($request->input('is_for'))) {
            try {
                $contact = Contact::where('account_id', auth()->user()->account_id)
                    ->where('id', $request->input('is_for'))
                    ->firstOrFail();
            } catch (ModelNotFoundException $e) {
                return $this->respondNotFound();
            }
        }

        try {
            $gift = Gift::create($request->all());
        } catch (QueryException $e) {
            return $this->respondNotTheRightParameters();
        }

        $gift->account_id = auth()->user()->account->id;
        $gift->save();

        return new GiftResource($gift);
    }

    /**
     * Update the gift.
     * @param  Request $request
     * @param  int $giftId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $giftId)
    {
        try {
            $gift = Gift::where('account_id', auth()->user()->account_id)
                ->where('id', $giftId)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        }

        // Validates basic fields to create the entry
        $validator = Validator::make($request->all(), [
            'is_for' => 'integer|nullable',
            'name' => 'required|string|max:255',
            'comment' => 'string|max:1000000|nullable',
            'url' => 'string|max:1000000|nullable',
            'value' => 'string|max:255',
            'is_an_idea' => 'boolean',
            'has_been_offered' => 'boolean',
            'date_offered' => 'date|nullable',
            'contact_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->setErrorCode(32)
                        ->respondWithError($validator->errors()->all());
        }

        try {
            $contact = Contact::where('account_id', auth()->user()->account_id)
                ->where('id', $request->input('contact_id'))
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        }

        if (! is_null($request->input('is_for'))) {
            try {
                $contact = Contact::where('account_id', auth()->user()->account_id)
                    ->where('id', $request->input('is_for'))
                    ->firstOrFail();
            } catch (ModelNotFoundException $e) {
                return $this->respondNotFound();
            }
        }

        try {
            $gift->update($request->all());
        } catch (QueryException $e) {
            return $this->respondNotTheRightParameters();
        }

        if (is_null($request->input('is_for'))) {
            $gift->is_for = null;
            $gift->save();
        }

        return new GiftResource($gift);
    }

    /**
     * Delete a gift.
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $giftId)
    {
        try {
            $gift = Gift::where('account_id', auth()->user()->account_id)
                ->where('id', $giftId)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        }

        $gift->delete();

        return $this->respondObjectDeleted($gift->id);
    }

    /**
     * Get the list of gifts for the given contact.
     *
     * @return \Illuminate\Http\Response
     */
    public function gifts(Request $request, $contactId)
    {
        try {
            $contact = Contact::where('account_id', auth()->user()->account_id)
                ->where('id', $contactId)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        }

        $gifts = $contact->gifts()
                ->paginate($this->getLimitPerPage());

        return GiftResource::collection($gifts);
    }
}
