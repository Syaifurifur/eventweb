<!DOCTYPE html>
<html lang="id" data-theme="light">

<x-head :title="$title ?? 'Login'" />

<body class="eh-login-body">
    <style>
        .eh-login-body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f4f7fb;
            color: #0f172a;
            min-height: 100vh;
        }
        .eh-login-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* ==================== LEFT HERO PANEL ==================== */
        .eh-hero-panel {
            display: none;
            position: relative;
            width: 57%;
            background:
                radial-gradient(ellipse 75% 65% at 78% 36%, rgba(37, 99, 235, 0.48) 0%, rgba(29, 78, 216, 0.22) 45%, transparent 75%),
                radial-gradient(ellipse 60% 55% at 22% 82%, rgba(79, 70, 229, 0.38) 0%, transparent 70%),
                radial-gradient(ellipse 50% 40% at 15% 15%, rgba(30, 64, 175, 0.45) 0%, transparent 65%),
                linear-gradient(145deg, #060d26 0%, #0b1948 45%, #071130 100%);
            color: #ffffff;
            overflow: hidden;
            padding: 2.5rem 3.25rem;
            flex-direction: column;
            justify-content: center;
        }
        @media (min-width: 1024px) {
            .eh-hero-panel {
                display: flex;
            }
        }
        @media (min-width: 1280px) {
            .eh-hero-panel {
                width: 58%;
                padding: 3rem 4rem;
            }
        }

        .eh-hero-inner {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 680px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }

        /* Background 3D Waves & Constellation SVG */
        .eh-hero-svg-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        /* Brand Header */
        .eh-brand-link {
            display: inline-flex;
            align-items: center;
            gap: 0.875rem;
            text-decoration: none;
            color: #ffffff;
            margin-bottom: 1.35rem;
        }
        .eh-brand-logo {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            object-fit: contain;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45), 0 0 20px rgba(59, 130, 246, 0.25);
        }
        .eh-brand-title {
            display: block;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            line-height: 1.15;
            color: #ffffff;
        }
        .eh-brand-sub {
            display: block;
            font-size: 0.82rem;
            color: #93c5fd;
            font-weight: 500;
            margin-top: 2px;
        }

        .eh-eyebrow {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #93c5fd;
            opacity: 0.92;
            margin-bottom: 0.5rem;
        }
        .eh-hero-heading {
            margin: 0 0 0.85rem;
            font-size: clamp(2.4rem, 3.5vw, 3.4rem);
            font-weight: 900;
            line-height: 1.08;
            letter-spacing: -0.03em;
            color: #ffffff;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        }
        .eh-hero-heading-gradient {
            display: block;
            background: linear-gradient(90deg, #38bdf8 0%, #60a5fa 38%, #a855f7 78%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            padding-bottom: 4px;
        }
        .eh-hero-desc {
            margin: 0 0 1.4rem;
            max-width: 460px;
            font-size: 0.98rem;
            line-height: 1.6;
            color: #cbd5e1;
            font-weight: 400;
        }

        /* 3 Feature Pills */
        .eh-feature-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1.35rem;
        }
        .eh-feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .eh-feature-icon {
            width: 44px;
            height: 44px;
            border-radius: 13px;
            background: linear-gradient(145deg, rgba(37, 99, 235, 0.38), rgba(15, 23, 62, 0.75));
            border: 1px solid rgba(96, 165, 250, 0.4);
            box-shadow: 0 8px 20px rgba(2, 6, 23, 0.35), inset 0 1px 1px rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #60a5fa;
            flex-shrink: 0;
        }
        .eh-feature-title {
            display: block;
            font-size: 0.84rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.25;
        }
        .eh-feature-sub {
            display: block;
            font-size: 0.73rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* Floating 3D Calendar Icon Badge */
        .eh-floating-cal-badge {
            position: absolute;
            top: 165px;
            right: 8px;
            width: 66px;
            height: 66px;
            border-radius: 19px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.55) 0%, rgba(29, 78, 216, 0.82) 100%);
            border: 1.5px solid rgba(147, 197, 253, 0.65);
            box-shadow:
                0 20px 40px rgba(2, 6, 23, 0.65),
                0 0 35px rgba(56, 189, 248, 0.45),
                inset 0 1px 2px rgba(255, 255, 255, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            transform: perspective(500px) rotateY(-12deg) rotateX(10deg) rotateZ(-11deg);
            z-index: 12;
            backdrop-filter: blur(10px);
        }

        /* ==================== BOTTOM ILLUSTRATION SCENE ==================== */
        .eh-scene {
            position: relative;
            display: grid;
            grid-template-columns: 1.38fr 0.92fr;
            gap: 1.35rem;
            align-items: center;
            margin-top: 0.25rem;
            padding-top: 0.5rem;
        }

        /* Ambient glow behind the tablet & cards */
        .eh-scene::before {
            content: "";
            position: absolute;
            top: 10%;
            left: 35%;
            width: 320px;
            height: 260px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.28) 0%, rgba(99, 102, 241, 0.18) 50%, transparent 72%);
            filter: blur(22px);
            pointer-events: none;
            z-index: 0;
        }

        /* 3D Perspective Tablet Mockup */
        .eh-tablet-outer {
            position: relative;
            z-index: 4;
            padding: 7px;
            border-radius: 26px;
            background: linear-gradient(135deg, rgba(96, 165, 250, 0.55) 0%, rgba(30, 58, 138, 0.4) 50%, rgba(15, 23, 42, 0.85) 100%);
            box-shadow:
                -15px 28px 60px rgba(2, 6, 23, 0.85),
                0 0 40px rgba(37, 99, 235, 0.28),
                inset 0 1px 2px rgba(255, 255, 255, 0.45);
            transform: perspective(1050px) rotateX(8deg) rotateY(9deg) rotateZ(-4.5deg);
            transform-origin: center center;
        }
        .eh-tablet-frame {
            position: relative;
            overflow: hidden;
            background: linear-gradient(165deg, #0d1b3e 0%, #071028 100%);
            border-radius: 20px;
            padding: 1.15rem 1.25rem 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
        }
        /* Glossy screen reflection */
        .eh-tablet-frame::after {
            content: "";
            position: absolute;
            top: -40%;
            left: -20%;
            width: 75%;
            height: 180%;
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.01) 60%, transparent 100%);
            transform: rotate(25deg);
            pointer-events: none;
        }

        .eh-tablet-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.7rem;
        }
        .eh-tablet-title {
            font-size: 0.92rem;
            font-weight: 800;
            color: #f8fafc;
            letter-spacing: -0.01em;
        }
        .eh-tablet-nav {
            display: flex;
            gap: 0.4rem;
        }
        .eh-tablet-nav span {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.06);
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .eh-cal-days-header {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            text-align: center;
            font-size: 0.66rem;
            font-weight: 600;
            color: #94a3b8;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.16);
        }
        .eh-cal-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            grid-auto-rows: 46px;
            border-left: 1px solid rgba(148, 163, 184, 0.1);
            border-top: 1px solid rgba(148, 163, 184, 0.1);
            position: relative;
        }
        .eh-cal-cell {
            border-right: 1px solid rgba(148, 163, 184, 0.1);
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            padding: 4px 6px;
            font-size: 0.64rem;
            font-weight: 600;
            color: #64748b;
            position: relative;
        }

        /* Vibrant Floating Event Pills inside Calendar */
        .eh-cal-pill {
            position: absolute;
            z-index: 6;
            border-radius: 10px;
            padding: 5px 9px;
            color: #ffffff;
            font-size: 0.68rem;
            font-weight: 700;
            line-height: 1.15;
            display: flex;
            flex-direction: column;
            justify-content: center;
            white-space: nowrap;
            backdrop-filter: blur(4px);
        }
        .eh-cal-pill-head {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .eh-cal-pill small {
            font-size: 0.56rem;
            font-weight: 500;
            opacity: 0.92;
            margin-top: 2px;
            padding-left: 1px;
        }
        .eh-pill-blue {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border: 1px solid rgba(147, 197, 253, 0.55);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.48);
        }
        .eh-pill-purple {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            border: 1px solid rgba(216, 180, 254, 0.55);
            box-shadow: 0 8px 18px rgba(124, 58, 237, 0.48);
        }
        .eh-pill-teal {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            border: 1px solid rgba(110, 231, 183, 0.55);
            box-shadow: 0 8px 18px rgba(16, 185, 129, 0.45);
        }

        /* Right Stacked Glass Widgets */
        .eh-side-widgets {
            position: relative;
            z-index: 6;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .eh-glass-card {
            background: linear-gradient(155deg, rgba(30, 48, 102, 0.72) 0%, rgba(12, 21, 52, 0.88) 100%);
            border: 1px solid rgba(96, 165, 250, 0.35);
            border-top: 1px solid rgba(147, 197, 253, 0.55);
            border-radius: 20px;
            padding: 1.05rem 1.15rem;
            backdrop-filter: blur(16px);
            box-shadow:
                0 20px 45px rgba(2, 6, 23, 0.65),
                inset 0 1px 1px rgba(255, 255, 255, 0.15);
            transform: perspective(900px) rotateY(-6deg) rotateX(3deg);
        }
        .eh-glass-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        .eh-glass-title {
            font-size: 0.82rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.01em;
        }
        .eh-glass-link {
            font-size: 0.66rem;
            font-weight: 600;
            color: #60a5fa;
            text-decoration: none;
        }
        .eh-upcoming-list {
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
        }
        .eh-upcoming-item {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.45rem 0.55rem;
            border-radius: 12px;
            background: rgba(15, 23, 42, 0.45);
            border: 1px solid rgba(148, 163, 184, 0.12);
        }
        .eh-up-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .eh-up-name {
            display: block;
            font-size: 0.76rem;
            font-weight: 700;
            color: #f8fafc;
            line-height: 1.2;
        }
        .eh-up-date {
            display: block;
            font-size: 0.64rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* Statistik Event Bar Widget */
        .eh-stats-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        .eh-bars-group {
            display: flex;
            align-items: flex-end;
            gap: 7px;
            height: 50px;
            flex: 1;
        }
        .eh-stat-bar {
            flex: 1;
            border-radius: 5px 5px 2px 2px;
            background: linear-gradient(180deg, #38bdf8 0%, #2563eb 100%);
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.3);
        }
        .eh-stat-highlight {
            text-align: right;
            flex-shrink: 0;
        }
        .eh-stat-plus {
            display: block;
            font-size: 1.25rem;
            font-weight: 900;
            color: #38bdf8;
            line-height: 1.05;
            text-shadow: 0 0 18px rgba(56, 189, 248, 0.45);
        }
        .eh-stat-sub {
            display: block;
            font-size: 0.64rem;
            color: #94a3b8;
            margin-top: 3px;
        }

        /* ==================== RIGHT FORM PANEL ==================== */
        .eh-form-panel {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            position: relative;
            background:
                radial-gradient(circle at 100% 0%, rgba(219, 234, 254, 0.6) 0%, transparent 45%),
                radial-gradient(circle at 0% 100%, rgba(224, 231, 255, 0.6) 0%, transparent 45%),
                #f5f7fb;
        }
        @media (min-width: 1024px) {
            .eh-form-panel {
                width: 43%;
                padding: 2.5rem 2.5rem;
            }
        }
        @media (min-width: 1280px) {
            .eh-form-panel {
                width: 42%;
            }
        }

        .eh-login-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.07), 0 4px 12px rgba(15, 23, 42, 0.03);
            padding: 2.5rem 2.25rem 2rem;
        }
        @media (max-width: 480px) {
            .eh-login-card {
                padding: 1.85rem 1.5rem 1.65rem;
                border-radius: 20px;
            }
        }

        .eh-card-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            margin-bottom: 1.65rem;
        }
        .eh-card-brand img {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            object-fit: contain;
        }
        .eh-card-brand-name {
            display: block;
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            line-height: 1.15;
        }
        .eh-card-brand-sub {
            display: block;
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
            margin-top: 1px;
        }

        .eh-card-title {
            margin: 0 0 0.45rem;
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }
        .eh-card-subtitle {
            margin: 0 0 1.65rem;
            font-size: 0.84rem;
            line-height: 1.55;
            color: #64748b;
        }

        .eh-field-group {
            margin-bottom: 1.15rem;
        }
        .eh-field-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.45rem;
        }
        .eh-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .eh-input-icon {
            position: absolute;
            left: 14px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            font-size: 1.1rem;
        }
        .eh-input {
            width: 100%;
            height: 46px;
            padding: 0 2.75rem 0 2.65rem;
            font-size: 0.875rem;
            color: #0f172a;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .eh-input::placeholder {
            color: #94a3b8;
        }
        .eh-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .eh-eye-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            padding: 4px;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        .eh-eye-btn:hover {
            color: #0f172a;
        }

        .eh-remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin: 1rem 0 1.35rem;
        }
        .eh-remember-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #475569;
            cursor: pointer;
            user-select: none;
        }
        .eh-remember-checkbox {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            accent-color: #2563eb;
            cursor: pointer;
        }
        .eh-forgot-link {
            font-size: 0.8rem;
            font-weight: 700;
            color: #2563eb;
            text-decoration: none;
        }
        .eh-forgot-link:hover {
            text-decoration: underline;
        }

        .eh-submit-btn {
            width: 100%;
            height: 46px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(90deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%);
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.28);
            transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.12s ease;
        }
        .eh-submit-btn:hover {
            filter: brightness(1.05);
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.36);
        }
        .eh-submit-btn:active {
            transform: translateY(1px);
        }

        .eh-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.25rem 0;
            color: #94a3b8;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .eh-divider::before,
        .eh-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .eh-secondary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
        .eh-secondary-btn {
            height: 42px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #1e293b;
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }
        .eh-secondary-btn:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .eh-card-footer {
            margin-top: 1.75rem;
            text-align: center;
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 500;
        }
    </style>

    <div class="eh-login-wrapper">
        <!-- ==================== LEFT VISUAL SHOWCASE ==================== -->
        <aside class="eh-hero-panel">
            <!-- Rich 3D Swooping Ribbons, Glowing Flares & Constellation SVG -->
            <svg class="eh-hero-svg-bg" viewBox="0 0 900 960" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
                <defs>
                    <linearGradient id="ribbon1" x1="0" y1="0" x2="900" y2="650" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#1e40af" stop-opacity="0.35"/>
                        <stop offset="55%" stop-color="#1d4ed8" stop-opacity="0.18"/>
                        <stop offset="100%" stop-color="#0f172a" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="ribbon2" x1="900" y1="80" x2="150" y2="850" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.28"/>
                        <stop offset="50%" stop-color="#1e3a8a" stop-opacity="0.2"/>
                        <stop offset="100%" stop-color="#090d24" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="constLine" x1="380" y1="530" x2="820" y2="160" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.1"/>
                        <stop offset="45%" stop-color="#38bdf8" stop-opacity="0.85"/>
                        <stop offset="80%" stop-color="#60a5fa" stop-opacity="0.95"/>
                        <stop offset="100%" stop-color="#a855f7" stop-opacity="0.25"/>
                    </linearGradient>
                    <radialGradient id="nodeGlow" cx="50%" cy="50%" r="50%">
                        <stop offset="0%" stop-color="#38bdf8" stop-opacity="1"/>
                        <stop offset="40%" stop-color="#3b82f6" stop-opacity="0.55"/>
                        <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
                    </radialGradient>
                </defs>

                <!-- Swooping 3D Wave Ribbons -->
                <path d="M-80 -40 C 240 60, 460 -30, 680 180 C 850 340, 620 680, 960 820 L 960 -40 Z" fill="url(#ribbon1)"/>
                <path d="M960 60 C 680 140, 560 320, 420 500 C 260 700, 80 740, -90 920 L 960 960 Z" fill="url(#ribbon2)"/>

                <!-- Connecting Constellation Curves -->
                <path d="M350 530 C 510 470, 640 390, 745 255" stroke="url(#constLine)" stroke-width="2"/>
                <path d="M440 500 C 550 380, 660 280, 820 130" stroke="url(#constLine)" stroke-width="1.5" stroke-dasharray="4 5" opacity="0.75"/>
                <path d="M745 340 L 685 495" stroke="url(#constLine)" stroke-width="1.4" opacity="0.55"/>
                <path d="M685 275 L 745 340" stroke="url(#constLine)" stroke-width="1.4" opacity="0.65"/>

                <!-- Glowing Constellation Nodes -->
                <circle cx="685" cy="275" r="24" fill="url(#nodeGlow)"/>
                <circle cx="685" cy="275" r="4.5" fill="#e0f2fe"/>

                <circle cx="745" cy="340" r="34" fill="url(#nodeGlow)"/>
                <circle cx="745" cy="340" r="6.5" fill="#ffffff"/>

                <circle cx="535" cy="395" r="14" fill="url(#nodeGlow)"/>
                <circle cx="535" cy="395" r="3.5" fill="#bae6fd"/>

                <circle cx="685" cy="495" r="16" fill="url(#nodeGlow)"/>
                <circle cx="685" cy="495" r="3.5" fill="#c4b5fd"/>

                <!-- Ambient Starlight Particles -->
                <circle cx="130" cy="430" r="1.5" fill="#93c5fd" opacity="0.45"/>
                <circle cx="280" cy="455" r="2" fill="#38bdf8" opacity="0.4"/>
                <circle cx="610" cy="180" r="1.8" fill="#bae6fd" opacity="0.5"/>
                <circle cx="490" cy="230" r="1.5" fill="#93c5fd" opacity="0.35"/>
                <circle cx="830" cy="420" r="2" fill="#60a5fa" opacity="0.45"/>
            </svg>

            <div class="eh-hero-inner">
                <!-- Floating 3D Calendar Badge -->
                <div class="eh-floating-cal-badge" aria-hidden="true">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#f0f9ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="3" ry="3"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                        <circle cx="8.5" cy="14.5" r="1.1" fill="#f0f9ff"></circle>
                        <circle cx="12" cy="14.5" r="1.1" fill="#f0f9ff"></circle>
                        <circle cx="15.5" cy="14.5" r="1.1" fill="#f0f9ff"></circle>
                    </svg>
                </div>

                <!-- Top Hero Copy -->
                <div class="eh-hero-top">
                    <a class="eh-brand-link" href="<?= site_url('dashboard') ?>" aria-label="EventHub">
                        <img class="eh-brand-logo" src="<?= base_url('assets/img/eventhub-mark.png') ?>" alt="EventHub">
                        <span>
                            <strong class="eh-brand-title">EventHub</strong>
                            <small class="eh-brand-sub">Integrated Scheduler</small>
                        </span>
                    </a>

                    <div class="eh-eyebrow">EVENTS &nbsp;&bull;&nbsp; PEOPLE &nbsp;&bull;&nbsp; IMPACT</div>

                    <h1 class="eh-hero-heading">
                        Kelola Event
                        <span class="eh-hero-heading-gradient">Lebih Terarah</span>
                    </h1>

                    <p class="eh-hero-desc">
                        Satu platform untuk menjadwalkan, mengelola, dan memantau seluruh kegiatan.
                    </p>

                    <!-- 3 Feature Badges -->
                    <div class="eh-feature-row">
                        <div class="eh-feature-item">
                            <div class="eh-feature-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2.5"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                            </div>
                            <div>
                                <strong class="eh-feature-title">Terencana</strong>
                                <span class="eh-feature-sub">Jadwal lebih rapi</span>
                            </div>
                        </div>

                        <div class="eh-feature-item">
                            <div class="eh-feature-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </div>
                            <div>
                                <strong class="eh-feature-title">Terintegrasi</strong>
                                <span class="eh-feature-sub">Kolaborasi lebih mudah</span>
                            </div>
                        </div>

                        <div class="eh-feature-item">
                            <div class="eh-feature-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                    <line x1="18" y1="20" x2="18" y2="10"/>
                                    <line x1="12" y1="20" x2="12" y2="4"/>
                                    <line x1="6" y1="20" x2="6" y2="14"/>
                                </svg>
                            </div>
                            <div>
                                <strong class="eh-feature-title">Berdampak</strong>
                                <span class="eh-feature-sub">Hasil lebih terukur</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom 3D Interactive Illustration Scene -->
                <div class="eh-scene" aria-hidden="true">
                    <!-- Left: 3D Tilted Tablet Calendar -->
                    <div class="eh-tablet-outer">
                        <div class="eh-tablet-frame">
                            <div class="eh-tablet-header">
                                <span class="eh-tablet-title">Kalender Event</span>
                                <div class="eh-tablet-nav">
                                    <span>&lsaquo;</span>
                                    <span>&rsaquo;</span>
                                </div>
                            </div>
                            <div class="eh-cal-days-header">
                                <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                            </div>
                            <div class="eh-cal-grid">
                                <!-- Row 1 -->
                                <div class="eh-cal-cell"></div>
                                <div class="eh-cal-cell"></div>
                                <div class="eh-cal-cell">1</div>
                                <div class="eh-cal-cell">2</div>
                                <div class="eh-cal-cell">3</div>
                                <div class="eh-cal-cell">4</div>
                                <div class="eh-cal-cell">5</div>

                                <!-- Row 2 -->
                                <div class="eh-cal-cell">6</div>
                                <div class="eh-cal-cell">
                                    7
                                    <div class="eh-cal-pill eh-pill-blue" style="top: 6px; left: 4px; width: 122px;">
                                        <span class="eh-cal-pill-head">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                                            Workshop
                                        </span>
                                        <small>09.00 - 12.00</small>
                                    </div>
                                </div>
                                <div class="eh-cal-cell">8</div>
                                <div class="eh-cal-cell">9</div>
                                <div class="eh-cal-cell">10</div>
                                <div class="eh-cal-cell">11</div>
                                <div class="eh-cal-cell">12</div>

                                <!-- Row 3 -->
                                <div class="eh-cal-cell">13</div>
                                <div class="eh-cal-cell">14</div>
                                <div class="eh-cal-cell">
                                    15
                                    <div class="eh-cal-pill eh-pill-purple" style="top: 6px; left: 8px; width: 120px;">
                                        <span class="eh-cal-pill-head">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                            Seminar
                                        </span>
                                        <small>10.00 - 12.00</small>
                                    </div>
                                </div>
                                <div class="eh-cal-cell">16</div>
                                <div class="eh-cal-cell">17</div>
                                <div class="eh-cal-cell">18</div>
                                <div class="eh-cal-cell">19</div>

                                <!-- Row 4 -->
                                <div class="eh-cal-cell">
                                    20
                                    <div class="eh-cal-pill eh-pill-blue" style="top: -4px; left: 4px; width: 118px;">
                                        <span class="eh-cal-pill-head">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                            Open House
                                        </span>
                                        <small>13.00 - 16.00</small>
                                    </div>
                                </div>
                                <div class="eh-cal-cell">21</div>
                                <div class="eh-cal-cell">22</div>
                                <div class="eh-cal-cell">
                                    23
                                    <div class="eh-cal-pill eh-pill-purple" style="top: 8px; left: 2px; width: 114px;">
                                        <span class="eh-cal-pill-head">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
                                            Edufair
                                        </span>
                                        <small>09.00 - 15.00</small>
                                    </div>
                                </div>
                                <div class="eh-cal-cell">24</div>
                                <div class="eh-cal-cell">
                                    25
                                    <div class="eh-cal-pill eh-pill-teal" style="top: -8px; left: -6px; width: 110px;">
                                        <span class="eh-cal-pill-head">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                            Talkshow
                                        </span>
                                        <small>13.00 - 15.00</small>
                                    </div>
                                </div>
                                <div class="eh-cal-cell">26</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Floating Glass Cards -->
                    <div class="eh-side-widgets">
                        <div class="eh-glass-card">
                            <div class="eh-glass-header">
                                <span class="eh-glass-title">Upcoming Event</span>
                                <span class="eh-glass-link">Lihat Semua &rarr;</span>
                            </div>
                            <div class="eh-upcoming-list">
                                <div class="eh-upcoming-item">
                                    <div class="eh-up-icon" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.35), rgba(109, 40, 217, 0.25)); border: 1px solid rgba(196, 181, 253, 0.35); color: #ddd6fe;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                            <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                                            <line x1="12" y1="19" x2="12" y2="23"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <strong class="eh-up-name">Seminar AI</strong>
                                        <span class="eh-up-date">29 Okt 2026</span>
                                    </div>
                                </div>

                                <div class="eh-upcoming-item">
                                    <div class="eh-up-icon" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.35), rgba(29, 78, 216, 0.25)); border: 1px solid rgba(147, 197, 253, 0.35); color: #bfdbfe;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                                            <line x1="16" y1="2" x2="16" y2="6"/>
                                            <line x1="8" y1="2" x2="8" y2="6"/>
                                            <line x1="3" y1="10" x2="21" y2="10"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <strong class="eh-up-name">BSI Flash 2027</strong>
                                        <span class="eh-up-date">15 - 20 Okt 2026</span>
                                    </div>
                                </div>

                                <div class="eh-upcoming-item">
                                    <div class="eh-up-icon" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.35), rgba(5, 150, 105, 0.25)); border: 1px solid rgba(110, 231, 183, 0.35); color: #a7f3d0;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                            <circle cx="9" cy="7" r="4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <strong class="eh-up-name">Workshop Digital</strong>
                                        <span class="eh-up-date">5 Nov 2026</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="eh-glass-card">
                            <div class="eh-glass-header" style="margin-bottom: 0.35rem;">
                                <span class="eh-glass-title">Statistik Event</span>
                            </div>
                            <div class="eh-stats-row">
                                <div class="eh-bars-group">
                                    <div class="eh-stat-bar" style="height: 36%; opacity: 0.55;"></div>
                                    <div class="eh-stat-bar" style="height: 52%; opacity: 0.68;"></div>
                                    <div class="eh-stat-bar" style="height: 46%; opacity: 0.72;"></div>
                                    <div class="eh-stat-bar" style="height: 70%; opacity: 0.84;"></div>
                                    <div class="eh-stat-bar" style="height: 85%; opacity: 0.92;"></div>
                                    <div class="eh-stat-bar" style="height: 100%;"></div>
                                </div>
                                <div class="eh-stat-highlight">
                                    <strong class="eh-stat-plus">+32%</strong>
                                    <span class="eh-stat-sub">dari bulan lalu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ==================== RIGHT LOGIN CARD ==================== -->
        <main class="eh-form-panel">
            <div class="eh-login-card">
                <a href="<?= site_url('dashboard') ?>" class="eh-card-brand">
                    <img src="<?= base_url('assets/img/eventhub-mark.png') ?>" alt="EventHub">
                    <span>
                        <strong class="eh-card-brand-name">EventHub</strong>
                        <small class="eh-card-brand-sub">Integrated Scheduler</small>
                    </span>
                </a>

                <h2 class="eh-card-title">Selamat datang kembali</h2>
                <p class="eh-card-subtitle">Silakan masukkan username/email dan password akun EventHub Anda.</p>

                <?php if (session('success')): ?>
                    <div class="alert success mb-4"><?= esc(session('success')) ?></div>
                <?php endif; ?>
                <?php if (session('error')): ?>
                    <div class="alert error mb-4"><?= esc(session('error')) ?></div>
                <?php endif; ?>

                <form action="<?= site_url('login') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="eh-field-group">
                        <label class="eh-field-label" for="eh-identity">Username atau Email</label>
                        <div class="eh-input-wrap">
                            <span class="eh-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="3"/>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                                </svg>
                            </span>
                            <input
                                type="text"
                                id="eh-identity"
                                name="identity"
                                value="<?= esc(old('identity')) ?>"
                                class="eh-input"
                                placeholder="admin@eventhub.test"
                                required
                            >
                        </div>
                    </div>

                    <div class="eh-field-group">
                        <label class="eh-field-label" for="your-password">Password</label>
                        <div class="eh-input-wrap password-field">
                            <span class="eh-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input
                                type="password"
                                name="password"
                                id="your-password"
                                class="eh-input"
                                placeholder="Masukkan password"
                                required
                            >
                            <button
                                type="button"
                                class="eh-eye-btn toggle-password ri-eye-line"
                                data-toggle="#your-password"
                                data-password-toggle
                                aria-label="Lihat password"
                                aria-pressed="false"
                            ></button>
                        </div>
                    </div>

                    <div class="eh-remember-row">
                        <label class="eh-remember-label" for="remember">
                            <input class="eh-remember-checkbox" type="checkbox" id="remember" name="remember">
                            <span>Ingat saya</span>
                        </label>
                        <a href="<?= site_url('forgot-password') ?>" class="eh-forgot-link">Lupa password?</a>
                    </div>

                    <button type="submit" class="eh-submit-btn">
                        <span>Masuk ke Dashboard</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </button>

                    <div class="eh-divider">atau</div>

                    <div class="eh-secondary-grid">
                        <a href="<?= site_url('register') ?>" class="eh-secondary-btn">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="8.5" cy="7" r="4"/>
                                <line x1="20" y1="8" x2="20" y2="14"/>
                                <line x1="23" y1="11" x2="17" y2="11"/>
                            </svg>
                            <span>Daftar User</span>
                        </a>
                        <a href="<?= site_url('crm/login') ?>" class="eh-secondary-btn">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="5" r="3"/>
                                <circle cx="5" cy="19" r="3"/>
                                <circle cx="19" cy="19" r="3"/>
                                <line x1="10.5" y1="7.5" x2="6.5" y2="16.5"/>
                                <line x1="13.5" y1="7.5" x2="17.5" y2="16.5"/>
                            </svg>
                            <span>Login CRM</span>
                        </a>
                    </div>

                    <div class="eh-card-footer">
                        &copy; <?= date('Y') ?> EventHub &bull; Integrated Scheduler
                    </div>
                </form>
            </div>
        </main>
    </div>

    <x-script />
</body>
</html>
