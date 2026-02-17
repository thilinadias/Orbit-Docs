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
            'content' => 'required|string',
        ]);

        $document = $organization->documents()->create($validated);

        return redirect()->route('documents.show', [$organization->slug, $document->id])->with('success', 'Document created.');
    }

    public function show(Request $request, $organization, Document $document)
    {
        $this->authorize('document.view');
        $currentOrganization = $request->attributes->get('current_organization');
        $htmlContent = Str::markdown($document->content);
        return view('documents.show', compact('currentOrganization', 'document', 'htmlContent'));
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
}
