<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Campaign\CampaignStoreRequest;
use App\Http\Requests\Campaign\CampaignUpdateRequest;
use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Campaign::class);

        $user = $request->user();
        $query = Campaign::query()
            ->with('owner')
            ->withCount('donations')
            ->withSum([
                'donations as amount_raised' => function ($q) {
                    $q->where('status', 'confirmed');
                }
            ], 'amount');

        if (!$user->can('campaign.viewAll')) {
            $query->where(function ($q) use ($user) {
                $q->where('status', 'published')
                    ->orWhere('owner_id', $user->id);
            });
        }

        if ($search = $request->string('q')->toString()) {
            $query->where(fn($q) => $q->where('title', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%"));
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($request->filled('min_goal')) {
            $query->where('goal_amount', '>=', (float) $request->query('min_goal'));
        }
        if ($request->filled('max_goal')) {
            $query->where('goal_amount', '<=', (float) $request->query('max_goal'));
        }
        if ($request->filled('deadline_before')) {
            $query->where('deadline', '<=', $request->query('deadline_before'));
        }
        if ($request->filled('deadline_after')) {
            $query->where('deadline', '>=', $request->query('deadline_after'));
        }

        $sort = (string) $request->query('sort', '-id');
        $allowed = [
            'id' => 'id',
            'created_at' => 'created_at',
            'deadline' => 'deadline',
            'goal_amount' => 'goal_amount',
            'amount_raised' => 'amount_raised',
        ];
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $columnKey = ltrim($sort, '-');
        $column = $allowed[$columnKey] ?? 'id';

        return CampaignResource::collection($query->orderBy($column, $direction)->paginate(15));
    }

    public function store(CampaignStoreRequest $request)
    {
        $campaign = Campaign::create([
            ...$request->validated(),
            'owner_id' => $request->user()->id,
        ]);

        return new CampaignResource($campaign->load('owner'));
    }

    public function show(Campaign $campaign)
    {
        $this->authorize('view', $campaign);
        return new CampaignResource($campaign->load('owner')->loadCount('donations'));
    }

    public function update(CampaignUpdateRequest $request, Campaign $campaign)
    {
        $this->authorize('update', $campaign);
        $campaign->update($request->validated());
        return new CampaignResource($campaign->load('owner')->loadCount('donations'));
    }

    public function destroy(Campaign $campaign)
    {
        $this->authorize('delete', $campaign);
        $campaign->delete();
        return response()->noContent();
    }

    public function publish(Campaign $campaign)
    {
        $this->authorize('publish', $campaign);
        $campaign->update(['status' => 'published']);
        return new CampaignResource($campaign->refresh()->load('owner')->loadCount('donations'));
    }
}


