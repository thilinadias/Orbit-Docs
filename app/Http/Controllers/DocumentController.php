<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('document.view');
        $organization = $request->attributes->get('current_organization');
        $documents = $organization->documents()->latest()->paginate(10);
        return view('documents.index', compact('organization', 'documents'));
    }

    public function create(Request $request)
    {
        $this->authorize('document.create');
        $organization = $request->attributes->get('current_organization');
        return view('documents.create', compact('organization'));
    }

    public function store(Request $request)
    {
        $this->authorize('document.create');
        $organization = $request->attributes->get('current_organization');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:524288', // 512MB limit
            'documentable_id' => 'nullable|integer',
            'documentable_type' => 'nullable|string',
        ]);

        $data = [
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'documentable_id' => $validated['documentable_id'] ?? null,
            'documentable_type' => $validated['documentable_type'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('documents', 'public');

            $data['is_upload'] = true;
            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getClientMimeType();
            $data['file_size'] = $file->getSize();
        }

        $document = $organization->documents()->create($data);

        if ($request->filled('documentable_type')) {
            $redirectRoute = $validated['documentable_type'] === 'App\Models\Site' ? 'sites.show' : 'assets.show';
            return redirect()->route($redirectRoute, [$organization->slug, $validated['documentable_id']])->with('success', 'Document uploaded.');
        }

        return redirect()->route('documents.show', [$organization->slug, $document->id])->with('success', 'Document created.');
    }

    public function show(Request $request, $organization, Document $document)
    {
        $this->authorize('document.view');
        $currentOrganization = $request->attributes->get('current_organization');

        $htmlContent = $document->is_upload ? null : Str::markdown($document->content ?? '');

        return view('documents.show', compact('currentOrganization', 'document', 'htmlContent'));
    }

    public function download(Request $request, $organization, Document $document)
    {
        $this->authorize('document.view');
        if (!$document->is_upload) {
            abort(404);
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function preview(Request $request, $organization, Document $document)
    {
        $this->authorize('document.view');
        if (!$document->is_upload) {
            abort(404);
        }

        return response()->file(storage_path('app/public/' . $document->file_path));
    }

    public function edit(Request $request, $organization, Document $document)
    {
        $this->authorize('document.edit');
        // Ensure organization is loaded as an object if route binding works, otherwise fetch it
        // The middleware should set 'current_organization', let's use that to be safe
        $organization = $request->attributes->get('current_organization');

        return view('documents.edit', compact('organization', 'document'));
    }

    public function update(Request $request, $organization, Document $document)
    {
        $this->authorize('document.edit');
        $organization = $request->attributes->get('current_organization');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'approval_status' => 'required|in:draft,review,published',
            'tags' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
        ]);

        // Save version before update
        $document->versions()->create([
            'content' => $document->content,
            'user_id' => auth()->id(),
        ]);

        $document->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'approval_status' => $validated['approval_status'],
            'category' => $validated['category'] ?? null,
            'author' => $validated['author'] ?? null,
        ]);

        // Handle Tags
        if (isset($validated['tags'])) {
            $tagNames = array_filter(array_map('trim', explode(',', $validated['tags'])));
            $tagIds = [];

            foreach ($tagNames as $tagName) {
                $tag = \App\Models\Tag::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName]
                );
                $tagIds[] = $tag->id;
            }

            $document->tags()->sync($tagIds);
        }
        else {
            $document->tags()->detach();
        }

        return redirect()->route('documents.show', [$organization->slug, $document->id])->with('success', 'Document updated.');
    }

    public function destroy(Request $request, $organization, Document $document)
    {
        $this->authorize('document.delete');
        $organization = $request->attributes->get('current_organization');

        // If it's an upload, delete the physical file
        if ($document->is_upload && $document->file_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index', $organization->slug)->with('success', 'Document deleted successfully.');
    }
}
