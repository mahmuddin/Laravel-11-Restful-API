<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactCreateRequest;
use App\Http\Requests\ContactUpdateRequest;
use App\Http\Resources\ContactCollection;
use App\Models\Contact;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Js;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ContactResource;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ContactController extends Controller
{
    public function create(ContactCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();
        $contact = new Contact($data);
        $contact->user_id = $user->id;
        $contact->save();
        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function get(int $id): ContactResource
    {
        $user = Auth::user();
        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json(['errors' => ['message' => ['not found']]], 404));
        }
        return new ContactResource($contact);
    }

    public function update(int $id, ContactUpdateRequest $request): ContactResource
    {
        $user = Auth::user();
        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json(['errors' => ['message' => ['not found']]], 404));
        }
        $data = $request->validated();
        $contact->fill($data);
        $contact->save();
        return new ContactResource($contact);
    }

    public function delete(int $id): JsonResponse
    {
        $user = Auth::user();
        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json(['errors' => ['message' => ['not found']]], 404));
        }
        $contact->delete();
        return response()->json(['data' => true], 200);
    }

    public function search(Request $request): ContactCollection
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);
        $contacts = Contact::where('user_id', $user->id)
            ->where(function (Builder $query) use ($request) {
                $name = $request->input('name');
                $phone = $request->input('phone');
                $email = $request->input('email');
                if ($name) {
                    $query->where(function (Builder $query) use ($name) {
                        $query->orWhere('first_name', 'like', '%' . $name . '%')
                            ->orWhere('last_name', 'like', '%' . $name . '%');
                    });
                }
                if ($phone) {
                    $query->where('phone', 'like', '%' . $phone . '%');
                }
                if ($email) {
                    $query->where('email', 'like', '%' . $email . '%');
                }
            })
            ->paginate(perPage: $size, page: $page);

        return new ContactCollection($contacts);
    }
}
