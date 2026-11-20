@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard-bg">

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert-box alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="alert-box alert-warning" style="margin-bottom: 1rem;">{{ session('warning') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert-box alert-error" style="margin-bottom: 1rem;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <!-- Welcome Bar -->
    <div class="welcome-bar">
        <div>
            <h1>Welcome back, {{ $user->name }}!</h1>
            <div class="sub-text">Logged in as {{ $user->email }} • User ID: #{{ $user->id }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}" data-prevent-duplicate style="display: inline;">
            @csrf
            <button type="submit" class="btn-logout">← Log Out</button>
        </form>
    </div>

    <!-- 4 Feature Info Boxes -->
    <div class="features-row">
        <div class="feature-box">
            <h4>1. Login Attempt Limit</h4>
            <p>Locked after 5 consecutive failed attempts for 60 seconds.</p>
        </div>
        <div class="feature-box">
            <h4>2. Auth Protection</h4>
            <p>Guests cannot view dashboard; logged-in users cannot access login/register.</p>
        </div>
        <div class="feature-box">
            <h4>3. Prevent Duplicate Clicks</h4>
            <p>Buttons disable immediately upon submit with animated spinner & debounce.</p>
        </div>
        <div class="feature-box">
            <h4>4. Prevent Form Resubmission</h4>
            <p>Post/Redirect/Get pattern prevents F5 refresh duplicates.</p>
        </div>
    </div>

    <!-- 3 Relationship Cards -->
    <div class="cards-row">

        <!-- Card 1: One-to-One (User <-> Profile) -->
        <div class="rel-card">
            <h2>One-to-One Relationship</h2>
            <div class="card-desc">
                Each user has exactly one Profile record in the profiles table with a unique foreign key constraint.
            </div>

            <!-- Current profile display -->
            <div class="card-info-block">
                <div class="info-label">Phone Number:</div>
                <div class="info-value">{{ $user->profile?->phone_number ?? 'Not set' }}</div>
            </div>
            <div class="card-info-block" style="margin-bottom: 1.2rem;">
                <div class="info-label">Bio:</div>
                <div class="info-value">{{ $user->profile?->bio ?? 'Not set' }}</div>
            </div>

            <!-- Update Profile Form -->
            <form method="POST" action="{{ route('profile.update') }}" data-prevent-duplicate>
                @csrf
                <div class="card-form-group">
                    <label>Update Phone Number</label>
                    <input type="text" name="phone_number" class="card-input" value="{{ old('phone_number', $user->profile?->phone_number) }}">
                </div>
                <div class="card-form-group">
                    <label>Update Bio</label>
                    <input type="text" name="bio" class="card-input" value="{{ old('bio', $user->profile?->bio) }}">
                </div>
                <button type="submit" class="btn-card btn-teal">Save Profile (0:0)</button>
            </form>
        </div>

        <!-- Card 2: One-to-Many (User <-> Posts) -->
        <div class="rel-card">
            <h2>One-to-Many Relationship</h2>
            <div class="card-desc">
                This user can author many Posts. Submitting a post uses PRG (Post/Redirect/Get) so refreshing F5 will never duplicate it.
            </div>

            <!-- Create New Post Form -->
            <form method="POST" action="{{ route('posts.store') }}" data-prevent-duplicate>
                @csrf
                <div class="card-form-group">
                    <label>New Post Title</label>
                    <input type="text" name="title" class="card-input" placeholder="e.g. My thoughts on SQLite..." value="{{ old('title') }}" required>
                </div>
                <div class="card-form-group">
                    <label>Post Body</label>
                    <textarea name="body" class="card-textarea" placeholder="Enter the post content here..." required>{{ old('body') }}</textarea>
                </div>
                <button type="submit" class="btn-card btn-dark-green">Publish Post (0:N)</button>
            </form>

            <!-- Posts List -->
            <div class="posts-header">
                <h3>YOUR POSTS</h3>
                <span>({{ $user->posts->count() }} TOTAL)</span>
            </div>
            <div class="posts-list">
                @forelse ($user->posts as $post)
                    <div class="post-item">
                        <div class="post-title">{{ $post->title }}</div>
                        <div class="post-body">{{ Str::limit($post->body, 80) }}</div>
                        <div class="post-meta">Post #{{ $post->id }} • {{ $post->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="empty-state">
                        No posts yet. Write your first post above!
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Card 3: Many-to-Many (User <-> Roles) -->
        <div class="rel-card">
            <h2>Many-to-Many Relationship</h2>
            <div class="card-desc">
                Users and Roles are connected through the role_user pivot table. A user can hold multiple roles at once.
            </div>

            <!-- Current Roles -->
            <div style="margin-bottom: 1rem;">
                <div style="font-size: 0.75rem; font-weight: 600; color: #666; margin-bottom: 0.4rem;">Assigned Roles:</div>
                @forelse ($user->roles as $role)
                    <span class="role-badge">{{ $role->name }}</span>
                @empty
                    <span style="font-size: 0.75rem; color: #999; font-style: italic;">No roles assigned.</span>
                @endforelse
            </div>

            <!-- Sync Roles Form -->
            <form method="POST" action="{{ route('roles.sync') }}" data-prevent-duplicate style="background: #f5f5ef; padding: 1rem; border-radius: 10px; border: 1px solid #ddd;">
                @csrf
                <div style="font-size: 0.75rem; font-weight: 700; color: #333; margin-bottom: 0.6rem;">Select Roles to Assign:</div>

                <div class="checkbox-list">
                    @foreach ($allRoles as $role)
                        <label>
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $role->id }}"
                                {{ $user->roles->contains($role->id) ? 'checked' : '' }}
                            >
                            {{ $role->name }}
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="btn-card btn-teal">Sync Roles (N:M)</button>
            </form>
        </div>

    </div>
</div>
@endsection
