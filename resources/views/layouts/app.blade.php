<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laravel Auth & Relationships System')</title>
    <style>
        /* ==============================
           GLOBAL RESET & BASE STYLES
           ============================== */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            color: #1a1a1a;
        }

        /* ==============================
           ABSTRACT GEOMETRIC BACKGROUND
           (Login & Register pages)
           ============================== */
        .auth-bg {
            background-color: #3a5a40;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 2rem;
        }
        /* Decorative shapes */
        .auth-bg::before,
        .auth-bg::after,
        .auth-bg .shape-1,
        .auth-bg .shape-2,
        .auth-bg .shape-3,
        .auth-bg .shape-4,
        .auth-bg .shape-5 {
            position: absolute;
            border-radius: 50%;
            z-index: 0;
        }
        /* Large dark-green circle, top-left */
        .auth-bg::before {
            content: '';
            width: 350px;
            height: 350px;
            background: #2d4a32;
            top: -60px;
            left: -80px;
        }
        /* Partial light gray circle, far right */
        .auth-bg::after {
            content: '';
            width: 300px;
            height: 300px;
            background: #c5c5c0;
            top: 30px;
            right: -120px;
        }
        .shape-1 {
            /* Large mint/light-green tilted ellipse, right-center */
            width: 260px;
            height: 420px;
            background: rgba(130, 220, 170, 0.55);
            top: 10%;
            right: 10%;
            transform: rotate(-25deg);
        }
        .shape-2 {
            /* Dark olive circle, bottom-left */
            width: 220px;
            height: 220px;
            background: #344e38;
            bottom: -40px;
            left: 5%;
        }
        .shape-3 {
            /* Maroon/brown circle, top-right */
            width: 180px;
            height: 180px;
            background: #6b3a3a;
            top: -20px;
            right: 5%;
        }
        .shape-4 {
            /* Small dark-green circle, bottom-right */
            width: 140px;
            height: 140px;
            background: #2d4a32;
            bottom: 10%;
            right: -30px;
        }
        .shape-5 {
            /* Light gray triangle shape (approximated as rotated square), bottom-right */
            width: 120px;
            height: 120px;
            background: #d5d5cf;
            bottom: -10px;
            right: 2%;
            border-radius: 10%;
            transform: rotate(35deg);
        }

        /* ==============================
           AUTH CARD (Login/Register)
           ============================== */
        .auth-card {
            background: rgba(180, 180, 150, 0.85);
            border-radius: 20px;
            padding: 2.5rem 2.5rem 2rem;
            width: 100%;
            max-width: 460px;
            position: relative;
            z-index: 10;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        }
        .auth-card.wide {
            max-width: 560px;
        }
        .auth-card h1 {
            text-align: center;
            font-size: 1.65rem;
            font-weight: 700;
            margin-bottom: 1.8rem;
            color: #1a1a1a;
            font-style: italic;
        }
        .auth-card.register h1 {
            font-style: normal;
        }

        /* ==============================
           FORM ELEMENTS
           ============================== */
        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-group label {
            display: block;
            font-weight: 700;
            font-size: 0.88rem;
            margin-bottom: 0.4rem;
            color: #1a1a1a;
        }
        .form-group label.center-label {
            text-align: center;
        }
        .form-input {
            width: 100%;
            padding: 0.65rem 1rem;
            border: 2px solid #d5d5cf;
            border-radius: 25px;
            font-size: 0.88rem;
            background: #ffffff;
            color: #333;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-input:focus {
            border-color: #5a8a60;
        }
        .form-input::placeholder {
            color: #aaa;
        }

        /* Password field with SHOW button */
        .password-wrapper {
            position: relative;
        }
        .password-wrapper .form-input {
            padding-right: 4.5rem;
        }
        .show-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: #e8e8e0;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 0.2rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 700;
            cursor: pointer;
            color: #555;
            letter-spacing: 0.5px;
        }
        .show-btn:hover {
            background: #d8d8d0;
        }

        /* Side-by-side grid */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Checkbox */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin: 0.8rem 0;
            justify-content: center;
        }
        .checkbox-group label {
            font-size: 0.8rem;
            font-weight: 400;
            color: #333;
        }

        /* ==============================
           BUTTONS
           ============================== */
        .btn {
            display: block;
            width: 65%;
            margin: 1.2rem auto 0;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 25px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 1px;
            transition: all 0.2s;
            text-align: center;
        }
        .btn-blue {
            background: #4a7cb5;
            color: #fff;
        }
        .btn-blue:hover {
            background: #3d6a9e;
        }
        .btn-light {
            background: #f0f0ea;
            color: #1a1a1a;
            border: 2px solid #d5d5cf;
        }
        .btn-light:hover {
            background: #e0e0d8;
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* ==============================
           AUTH CARD FOOTER LINK
           ============================== */
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: #333;
        }
        .auth-footer a {
            font-weight: 800;
            color: #1a1a1a;
            text-decoration: none;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }

        /* ==============================
           ERROR / FLASH MESSAGES
           ============================== */
        .alert-box {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            margin-bottom: 1.2rem;
            font-size: 0.82rem;
        }
        .alert-error {
            background: rgba(180, 60, 60, 0.15);
            border: 1px solid rgba(180, 60, 60, 0.3);
            color: #6b2020;
        }
        .alert-success {
            background: rgba(60, 140, 70, 0.15);
            border: 1px solid rgba(60, 140, 70, 0.3);
            color: #1a5a20;
        }
        .alert-warning {
            background: rgba(180, 150, 40, 0.15);
            border: 1px solid rgba(180, 150, 40, 0.3);
            color: #6b5a10;
        }

        /* ==============================
           DASHBOARD LAYOUT
           ============================== */
        .dashboard-bg {
            background: #7a8a6a;
            min-height: 100vh;
            padding: 1.5rem;
        }

        /* Welcome Bar */
        .welcome-bar {
            background: #2d3a2e;
            border-radius: 14px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .welcome-bar h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #c8d8b0;
        }
        .welcome-bar .sub-text {
            font-size: 0.78rem;
            color: #9aaa85;
            margin-top: 0.25rem;
        }
        .btn-logout {
            background: #3a5a40;
            color: #d0dfc0;
            border: none;
            border-radius: 10px;
            padding: 0.55rem 1.2rem;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: background 0.2s;
        }
        .btn-logout:hover {
            background: #4a6a50;
        }

        /* Feature Info Boxes Row */
        .features-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.8rem;
            margin-bottom: 1.5rem;
        }
        .feature-box {
            background: #c8c8a8;
            border: 1px solid #b0b090;
            border-radius: 10px;
            padding: 0.9rem 1rem;
        }
        .feature-box h4 {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.35rem;
            color: #2a3a2a;
        }
        .feature-box p {
            font-size: 0.68rem;
            color: #444;
            line-height: 1.4;
        }

        /* Relationship Cards Row */
        .cards-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.2rem;
        }
        .rel-card {
            background: #e8e8d8;
            border: 1px solid #c8c8b0;
            border-radius: 14px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }
        .rel-card h2 {
            text-align: center;
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
            color: #1a1a1a;
        }
        .rel-card .card-desc {
            font-size: 0.72rem;
            color: #555;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        /* Card inner sections */
        .card-info-block {
            background: #f5f5ef;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 0.8rem 1rem;
            margin-bottom: 1rem;
        }
        .card-info-block .info-label {
            font-size: 0.72rem;
            font-weight: 600;
            color: #666;
        }
        .card-info-block .info-value {
            font-size: 0.82rem;
            color: #333;
        }

        /* Card form inputs */
        .card-form-group {
            margin-bottom: 0.8rem;
        }
        .card-form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.3rem;
        }
        .card-input {
            width: 100%;
            padding: 0.5rem 0.8rem;
            border: 1px solid #ccc;
            border-radius: 20px;
            font-size: 0.78rem;
            background: #fff;
            outline: none;
        }
        .card-input:focus {
            border-color: #5a8a60;
        }
        .card-textarea {
            width: 100%;
            padding: 0.5rem 0.8rem;
            border: 1px solid #ccc;
            border-radius: 12px;
            font-size: 0.78rem;
            background: #fff;
            outline: none;
            resize: vertical;
            min-height: 50px;
        }
        .card-textarea:focus {
            border-color: #5a8a60;
        }

        /* Card buttons */
        .btn-card {
            display: block;
            width: 100%;
            padding: 0.55rem 1rem;
            border: none;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            letter-spacing: 0.5px;
            transition: all 0.2s;
            margin-top: 0.6rem;
        }
        .btn-card:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .btn-teal {
            background: #3a6a5a;
            color: #fff;
        }
        .btn-teal:hover {
            background: #2d5a4a;
        }
        .btn-dark-green {
            background: #2d4a38;
            color: #fff;
        }
        .btn-dark-green:hover {
            background: #1e3a28;
        }

        /* Roles badge */
        .role-badge {
            display: inline-block;
            background: #3a6a5a;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.25rem 0.7rem;
            border-radius: 6px;
            margin: 0.15rem 0.2rem;
        }

        /* Posts list */
        .posts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.8rem;
            margin-bottom: 0.5rem;
        }
        .posts-header h3 {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #333;
        }
        .posts-header span {
            font-size: 0.7rem;
            color: #888;
        }
        .posts-list {
            max-height: 180px;
            overflow-y: auto;
        }
        .post-item {
            background: #f5f5ef;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 0.6rem 0.8rem;
            margin-bottom: 0.5rem;
        }
        .post-item .post-title {
            font-size: 0.78rem;
            font-weight: 700;
            color: #1a1a1a;
        }
        .post-item .post-body {
            font-size: 0.72rem;
            color: #666;
            margin-top: 0.2rem;
        }
        .post-item .post-meta {
            font-size: 0.62rem;
            color: #999;
            margin-top: 0.3rem;
        }
        .empty-state {
            background: #f5f5ef;
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            font-size: 0.75rem;
            color: #999;
        }

        /* Checkbox list */
        .checkbox-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .checkbox-list label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #333;
            cursor: pointer;
        }
        .checkbox-list input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #3a6a5a;
        }

        /* Spinner */
        .spinner {
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 2px solid #ffffff;
            width: 1rem;
            height: 1rem;
            animation: spin 0.8s linear infinite;
            display: inline-block;
            vertical-align: middle;
            margin-right: 0.5rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* ==============================
           RESPONSIVE
           ============================== */
        @media (max-width: 900px) {
            .features-row { grid-template-columns: repeat(2, 1fr); }
            .cards-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .features-row { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
            .welcome-bar { padding: 1rem 1.2rem; }
            .welcome-bar h1 { font-size: 1.2rem; }
        }
    </style>
</head>
<body>
    @yield('content')

    <!-- Requirement 3: Client-side Duplicate Submission Prevention -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form[data-prevent-duplicate]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.isSubmitting === 'true') {
                        event.preventDefault();
                        return false;
                    }
                    form.dataset.isSubmitting = 'true';
                    var submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.dataset.originalHtml = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
                    }
                });
            });

            // Password show/hide toggle
            document.querySelectorAll('.show-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var input = btn.parentElement.querySelector('input');
                    if (input.type === 'password') {
                        input.type = 'text';
                        btn.textContent = 'HIDE';
                    } else {
                        input.type = 'password';
                        btn.textContent = 'SHOW';
                    }
                });
            });
        });
    </script>
</body>
</html>
