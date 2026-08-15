<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard | ISU Library System</title>
    <link rel="stylesheet" href="{{ asset('css/isu-ui-fix.css') }}">
    <style>
        :root {
            --green: #0b6b3a;
            --green-deep: #064225;
            --gold: #f2c84b;
            --red: #c8282d;
            --bg: #f4f8f1;
            --panel: rgba(255, 255, 255, 0.92);
            --text: #173423;
            --muted: #617265;
            --line: rgba(11, 107, 58, 0.14);
            --sidebar-width: clamp(17rem, 22vw, 18.5rem);
            --shadow: 0 1.1rem 2.8rem rgba(6, 66, 37, 0.10);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            font-size: 14px;
        }

        body {
            margin: 0;
            min-height: 100dvh;
            color: var(--text);
            font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            font-size: clamp(0.98rem, 0.45vw + 0.85rem, 1.08rem);
            line-height: clamp(1.45, 1.1 + 0.2vw, 1.7);
            background:
                radial-gradient(circle at top left, rgba(242, 200, 75, 0.18), transparent 24rem),
                radial-gradient(circle at top right, rgba(11, 107, 58, 0.12), transparent 18rem),
                linear-gradient(180deg, #f8fbf5 0%, var(--bg) 100%);
            overflow-x: hidden;
        }

        a {
            color: inherit;
        }

        .skip-link {
            position: absolute;
            left: -9999px;
            top: auto;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }

        .skip-link:focus {
            left: 1rem;
            top: 1rem;
            width: auto;
            height: auto;
            z-index: 60;
            padding: 0.7rem 1rem;
            border-radius: 999px;
            background: #fff;
            box-shadow: var(--shadow);
            color: var(--green-deep);
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: clamp(0.8rem, 1.5vw, 1.2rem);
            padding: clamp(0.9rem, 2vw, 1.2rem) clamp(1rem, 3vw, 2.2rem);
            background: rgba(6, 66, 37, 0.94);
            color: #fff;
            backdrop-filter: blur(16px);
            box-shadow: 0 10px 30px rgba(6, 66, 37, 0.16);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            text-decoration: none;
        }

        .brand img {
            width: clamp(3rem, 5vw, 3.8rem);
            height: clamp(3rem, 5vw, 3.8rem);
            object-fit: cover;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 12px 26px rgba(0, 0, 0, 0.15);
        }

        .brand-copy strong,
        .brand-copy span {
            display: block;
        }

        .brand-copy strong {
            font-size: 1.02rem;
        }

        .brand-copy span {
            margin-top: 0.15rem;
            color: rgba(255, 255, 255, 0.75);
            font-size: clamp(0.8rem, 0.35vw + 0.72rem, 0.95rem);
        }

        .topbar-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            padding: clamp(0.45rem, 1vw, 0.7rem) clamp(0.7rem, 1.2vw, 1rem);
            background: rgba(255, 255, 255, 0.08);
            font-size: clamp(0.8rem, 0.35vw + 0.72rem, 0.95rem);
            color: rgba(255, 255, 255, 0.95);
            white-space: nowrap;
        }

        .logout {
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 999px;
            padding: clamp(0.55rem, 1vw, 0.82rem) clamp(0.85rem, 1.4vw, 1.15rem);
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .logout:hover,
        .logout:focus-visible {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.16);
            outline: none;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            min-height: calc(100dvh - clamp(4.75rem, 7vw, 6rem));
        }

        .sidebar {
            position: fixed;
            top: clamp(4.75rem, 7vw, 6rem);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100dvh - clamp(4.75rem, 7vw, 6rem));
            overflow-y: auto;
            padding: clamp(0.9rem, 2vw, 1.2rem);
            border-right: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.62);
            backdrop-filter: blur(10px);
        }

        .sidebar-card {
            display: grid;
            gap: clamp(0.85rem, 1.6vw, 1rem);
            border: 1px solid var(--line);
            border-radius: 1.25rem;
            padding: clamp(0.9rem, 1.8vw, 1rem);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(245, 250, 242, 0.94));
            box-shadow: var(--shadow);
        }

        .sidebar-header {
            display: grid;
            gap: 0.65rem;
        }

        .sidebar-header img {
            width: clamp(3.25rem, 5vw, 4rem);
            height: clamp(3.25rem, 5vw, 4rem);
            object-fit: cover;
            border-radius: 1rem;
            background: #fff;
        }

        .sidebar-header h2,
        .sidebar-header p {
            margin: 0;
        }

        .sidebar-header h2 {
            color: var(--green-deep);
            font-size: clamp(1.05rem, 1vw + 0.85rem, 1.35rem);
        }

        .sidebar-header p {
            color: var(--muted);
            line-height: clamp(1.45, 1.1 + 0.15vw, 1.65);
        }

        .nav-group {
            display: grid;
            gap: 0.45rem;
        }

        .nav-group-label {
            margin: 0.25rem 0 0;
            color: var(--muted);
            font-size: clamp(0.72rem, 0.35vw + 0.64rem, 0.85rem);
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .nav-toggle,
        .nav-link {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border: 0;
            border-radius: 0.95rem;
            padding: clamp(0.75rem, 1.2vw, 0.95rem) clamp(0.85rem, 1.2vw, 1rem);
            background: transparent;
            color: var(--green-deep);
            font: inherit;
            font-weight: 700;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .nav-toggle:hover,
        .nav-toggle:focus-visible,
        .nav-group.open .nav-toggle,
        .nav-link:hover,
        .nav-link:focus-visible,
        .nav-link.active {
            color: #fff;
            background: linear-gradient(135deg, var(--green), #0a7a43);
            transform: translateX(2px);
            outline: none;
        }

        .nav-caret {
            display: inline-flex;
            transition: transform 0.2s ease;
        }

        .nav-group.open .nav-caret {
            transform: rotate(180deg);
        }

        .nav-submenu {
            display: grid;
            gap: 0.45rem;
            padding-left: 0.45rem;
        }

        .nav-group:not(.open) .nav-submenu {
            display: none;
        }

        .nav-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.75rem;
            padding: clamp(0.18rem, 0.35vw, 0.24rem) clamp(0.4rem, 0.8vw, 0.55rem);
            border-radius: 999px;
            background: rgba(11, 107, 58, 0.1);
            color: var(--green);
            font-size: clamp(0.72rem, 0.35vw + 0.64rem, 0.85rem);
            font-weight: 800;
        }

        .nav-link.active .nav-badge,
        .nav-link:hover .nav-badge,
        .nav-link:focus-visible .nav-badge,
        .nav-toggle:hover .nav-badge,
        .nav-toggle:focus-visible .nav-badge {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
        }

        main {
            margin-left: var(--sidebar-width);
            padding: clamp(1rem, 2.5vw, 2rem);
            min-height: calc(100dvh - clamp(4.75rem, 7vw, 6rem));
            overflow-y: auto;
            width: calc(100% - var(--sidebar-width));
        }

        .shell {
            display: grid;
            gap: clamp(1rem, 2vw, 1.35rem);
            width: 100%;
            max-width: min(100%, 90rem);
            margin-inline: auto;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        [data-view] {
            display: none;
        }

        [data-view].visible {
            display: block;
        }

        .hero {
            display: grid;
            gap: clamp(0.9rem, 1.5vw, 1.2rem);
            padding: clamp(1.1rem, 3vw, 1.7rem);
            border: 1px solid var(--line);
            border-radius: 1.4rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(245, 250, 242, 0.8));
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
        }

        .hero::after {
            content: "";
            position: absolute;
            inset: auto -6rem -5rem auto;
            width: clamp(12rem, 24vw, 18rem);
            height: clamp(12rem, 24vw, 18rem);
            border-radius: 50%;
            background: radial-gradient(circle, rgba(242, 200, 75, 0.34), transparent 68%);
            pointer-events: none;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: clamp(0.38rem, 0.8vw, 0.48rem) clamp(0.62rem, 1vw, 0.75rem);
            border-radius: 999px;
            background: rgba(11, 107, 58, 0.1);
            color: var(--green);
            font-size: clamp(0.72rem, 0.35vw + 0.65rem, 0.88rem);
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: clamp(0.55rem, 1vw, 0.7rem) 0 0;
            color: var(--green-deep);
            font-size: clamp(1.9rem, 4.2vw, 3.6rem);
            line-height: clamp(1.02, 0.95 + 0.1vw, 1.12);
        }

        .hero p {
            max-width: min(100%, 47.5rem);
            margin: clamp(0.7rem, 1.2vw, 0.95rem) 0 0;
            color: var(--muted);
            line-height: clamp(1.5, 1.15 + 0.2vw, 1.85);
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: clamp(0.55rem, 1vw, 0.75rem);
            margin-top: clamp(0.8rem, 1.5vw, 1rem);
        }

        .action-link,
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: clamp(2.75rem, 4vw, 3rem);
            padding: 0.72rem 1rem;
            border-radius: 0.95rem;
            border: 1px solid transparent;
            font: inherit;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .action-link:hover,
        .action-link:focus-visible,
        .btn:hover,
        .btn:focus-visible {
            transform: translateY(-1px);
            outline: none;
        }

        .btn-green,
        .action-link.primary {
            background: linear-gradient(135deg, var(--green), #0a7a43);
            color: #fff;
            box-shadow: 0 12px 26px rgba(11, 107, 58, 0.22);
        }

        .btn-gold {
            background: linear-gradient(135deg, #e5b927, var(--gold));
            color: var(--green-deep);
        }

        .btn-blue {
            background: linear-gradient(135deg, #275e8a, #1e4670);
            color: #fff;
        }

        .btn-red {
            background: linear-gradient(135deg, var(--red), #9e2226);
            color: #fff;
        }

        .btn-ghost,
        .action-link.secondary {
            background: rgba(255, 255, 255, 0.92);
            color: var(--green-deep);
            border-color: var(--line);
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(clamp(13rem, 20vw, 16rem), 1fr));
            gap: clamp(0.85rem, 1.8vw, 1.15rem);
        }

        .status-card,
        .panel,
        .stack-card,
        .mini-card {
            border: 1px solid var(--line);
            border-radius: 1.2rem;
            background: var(--panel);
            box-shadow: var(--shadow);
        }

        .status-card {
            display: grid;
            gap: 0.85rem;
            min-height: clamp(9rem, 16vw, 11rem);
            padding: clamp(1rem, 2vw, 1.25rem);
        }

        .status-card .label {
            color: var(--muted);
            font-size: clamp(0.74rem, 0.35vw + 0.66rem, 0.88rem);
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .status-card .count {
            color: var(--green-deep);
            font-size: clamp(2rem, 4vw, 3.4rem);
            font-weight: 900;
            line-height: clamp(0.92, 0.88 + 0.06vw, 1);
        }

        .status-card p {
            margin: 0;
            color: var(--muted);
            line-height: clamp(1.45, 1.1 + 0.15vw, 1.65);
        }

        .status-card.green {
            border-top: 0.45rem solid var(--green);
        }

        .status-card.gold {
            border-top: 0.45rem solid var(--gold);
        }

        .status-card.orange {
            border-top: 0.45rem solid #e67e22;
        }

        .status-card.red {
            border-top: 0.45rem solid var(--red);
        }

        .panel {
            overflow: hidden;
        }

        .panel-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            padding: clamp(1rem, 2vw, 1.15rem) clamp(1rem, 2vw, 1.2rem) clamp(0.85rem, 1.5vw, 0.95rem);
            border-bottom: 1px solid var(--line);
            flex-wrap: wrap;
        }

        .panel-header h2 {
            margin: 0;
            color: var(--green-deep);
            font-size: clamp(1.25rem, 2vw, 1.7rem);
        }

        .panel-header p {
            margin: 0.35rem 0 0;
            color: var(--muted);
        }

        .panel-subtle {
            padding: 0 clamp(1rem, 2vw, 1.2rem) clamp(1rem, 2vw, 1.2rem);
        }

        .toolbar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(clamp(14rem, 24vw, 18rem), 1fr));
            gap: 0.75rem;
            align-items: center;
            padding: clamp(0.9rem, 2vw, 1rem) clamp(1rem, 2vw, 1.2rem) clamp(1rem, 2vw, 1.2rem);
        }

        .toolbar-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }

        .toolbar-filter input[type="search"],
        .toolbar-filter select {
            flex: 1 1 220px;
            min-width: 0;
        }

        .search-form {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .table-wrap {
            overflow-x: auto;
            overflow-y: auto;
            max-height: min(58vh, 34rem);
            border: 1px solid var(--line);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.94);
        }

        .table-wrap thead th {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 100%;
        }

        th,
        td {
            padding: clamp(0.75rem, 1.2vw, 0.95rem) clamp(0.85rem, 1.4vw, 1rem);
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #fbf1d9;
            color: var(--green-deep);
            font-size: clamp(0.72rem, 0.35vw + 0.64rem, 0.85rem);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: clamp(0.18rem, 0.35vw, 0.24rem) clamp(0.45rem, 0.8vw, 0.55rem);
            background: rgba(242, 200, 75, 0.22);
            color: var(--green-deep);
            font-size: 0.82rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .badge.good {
            background: rgba(11, 107, 58, 0.12);
            color: var(--green);
        }

        .badge.warn {
            background: rgba(230, 126, 34, 0.16);
            color: #b45c11;
        }

        .badge.danger {
            background: rgba(200, 40, 45, 0.13);
            color: var(--red);
        }

        .stars { display: inline-flex; align-items: center; gap: 0.1rem; white-space: nowrap; }
        .star { color: var(--gold); font-size: 1rem; line-height: 1; }
        .star.empty { color: var(--line); }
        .rating-value { color: var(--text); font-weight: 600; font-size: 0.85rem; margin-left: 0.35rem; }
        .muted { color: var(--muted); }

        .empty-state,
        .flash {
            margin: 0 clamp(1rem, 2vw, 1.2rem) clamp(1rem, 2vw, 1.2rem);
            border: 1px solid var(--line);
            border-radius: 1rem;
            padding: clamp(0.9rem, 1.8vw, 1rem) clamp(0.95rem, 1.8vw, 1.05rem);
            background: rgba(255, 255, 255, 0.9);
        }

        .flash {
            color: var(--green-deep);
            background: rgba(242, 200, 75, 0.18);
            font-weight: 700;
        }

        .cards-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(clamp(15rem, 30vw, 22rem), 1fr));
            gap: clamp(0.85rem, 1.8vw, 1.1rem);
            padding: 0 clamp(1rem, 2vw, 1.2rem) clamp(1rem, 2vw, 1.2rem);
        }

        .cards-2.activity-stack {
            grid-template-columns: 1fr;
        }

        .cards-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(clamp(15rem, 28vw, 20rem), 1fr));
            gap: clamp(0.85rem, 1.8vw, 1.1rem);
            padding: 0 clamp(1rem, 2vw, 1.2rem) clamp(1rem, 2vw, 1.2rem);
        }

        .stack-card {
            padding: clamp(0.95rem, 1.8vw, 1.1rem);
        }

        .stack-card h3 {
            margin: 0 0 0.55rem;
            color: var(--green-deep);
        }

        .stack-card p,
        .stack-card ul {
            margin: 0;
            color: var(--muted);
            line-height: clamp(1.45, 1.1 + 0.2vw, 1.75);
        }

        .stack-card ul {
            padding-left: 1.05rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .field {
            display: grid;
            gap: 0.4rem;
        }

        .strength-meter {
            display: grid;
            gap: 0.35rem;
            margin-top: 0.15rem;
        }

        .strength-bar {
            height: 6px;
            border-radius: 999px;
            background: rgba(11, 107, 58, 0.12);
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0;
            border-radius: 999px;
            background: #ef4444;
            transition: all 0.2s ease;
        }

        .strength-text {
            font-size: 0.82rem;
            color: var(--muted);
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .field label {
            color: var(--green-deep);
            font-size: clamp(0.82rem, 0.35vw + 0.74rem, 0.95rem);
            font-weight: 700;
        }

        input,
        select,
        textarea {
            width: 100%;
            min-height: clamp(2.75rem, 4vw, 3rem);
            border: 1px solid var(--line);
            border-radius: 0.9rem;
            padding: 0.72rem 0.9rem;
            background: rgba(255, 255, 255, 0.96);
            color: var(--text);
            font: inherit;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 4px rgba(11, 107, 58, 0.12);
            outline: none;
        }

        textarea {
            min-height: clamp(7rem, 12vw, 8rem);
            resize: vertical;
        }

        .pill-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            margin-bottom: 0.9rem;
        }

        .work-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: clamp(0.34rem, 0.7vw, 0.44rem) clamp(0.62rem, 1vw, 0.75rem);
            border-radius: 999px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.94);
            color: var(--green-deep);
            font-size: clamp(0.78rem, 0.35vw + 0.7rem, 0.9rem);
            font-weight: 700;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: clamp(0.9rem, 1.8vw, 1rem);
            background: rgba(10, 24, 14, 0.58);
            z-index: 80;
        }

        .modal-backdrop.open {
            display: flex;
        }

        .modal-card {
            width: min(100%, 47.5rem);
            max-height: min(92dvh, 53.75rem);
            overflow: auto;
            border: 1px solid var(--line);
            border-radius: 1.3rem;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.15rem;
            border-bottom: 1px solid var(--line);
        }

        .modal-header h3 {
            margin: 0;
            color: var(--green-deep);
        }

        .modal-header p {
            margin: 0.35rem 0 0;
            color: var(--muted);
        }

        .modal-close {
            border: 0;
            border-radius: 999px;
            background: rgba(11, 107, 58, 0.08);
            color: var(--green-deep);
            font-size: 1.35rem;
            line-height: 1;
            width: 2.4rem;
            height: 2.4rem;
            cursor: pointer;
        }

        .modal-body {
            padding: 1rem 1.15rem 1.15rem;
        }

        .modal-actions {
            display: flex;
            gap: 0.65rem;
            flex-wrap: wrap;
            justify-content: flex-end;
            margin-top: 1rem;
        }

        .mini-list {
            display: grid;
            gap: 0.75rem;
        }

        .mini-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.9rem 0.95rem;
            border-color: var(--line);
        }

        .mini-card--unread {
            border-color: var(--gold);
        }

        .mini-card strong {
            display: block;
            color: var(--green-deep);
        }

        .mini-card span {
            color: var(--muted);
            font-size: clamp(0.82rem, 0.35vw + 0.74rem, 0.95rem);
        }

        .section-anchor {
            scroll-margin-top: 7rem;
        }

        @media (max-width: 1024px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                width: auto;
                height: auto;
                overflow-y: visible;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            main {
                margin-left: 0;
                min-height: auto;
                width: 100%;
            }

            .status-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .cards-3 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .topbar-meta {
                justify-content: flex-start;
            }

            .toolbar,
            .cards-2,
            .cards-3,
            .form-grid {
                grid-template-columns: 1fr;
            }

            .status-grid {
                grid-template-columns: 1fr;
            }

            .panel-header {
                align-items: flex-start;
            }

            table {
                min-width: 100%;
            }
        }

        .chat-launcher, .chat-close {
            position: fixed;
            right: 1.25rem;
            bottom: 1.25rem;
            z-index: 80;
            border: 0;
            border-radius: 999px;
            padding: 0.95rem 1.1rem;
            background: linear-gradient(135deg, var(--green), #0a7a43);
            color: #fff;
            font: inherit;
            font-weight: 900;
            box-shadow: 0 18px 36px rgba(11, 107, 58, 0.28);
            cursor: pointer;
        }
        .chat-panel { position: fixed; right: 1.25rem; bottom: 5.25rem; z-index: 80; width: min(380px, calc(100vw - 2.5rem)); max-height: min(70vh, 680px); display: none; grid-template-rows: auto 1fr auto; border: 1px solid var(--line); border-radius: 1.25rem; overflow: hidden; background: rgba(255,255,255,.98); box-shadow: 0 28px 80px rgba(6,66,37,.22); }
        .chat-panel.open { display: grid; }
        .chat-head { display: flex; justify-content: space-between; gap: 1rem; padding: 1rem 1.05rem; background: linear-gradient(135deg, var(--green-deep), var(--green)); color: #fff; }
        .chat-head h3, .chat-head p { margin: 0; }
        .chat-head p { margin-top: .2rem; color: rgba(255,255,255,.8); font-size: .85rem; }
        .chat-close { position: static; width: 2.2rem; height: 2.2rem; padding: 0; background: rgba(255,255,255,.14); }
        .chat-log { display: grid; gap: .75rem; padding: 1rem; overflow: auto; background: radial-gradient(circle at top left, rgba(242,200,75,.10), transparent 12rem), #fbfcf8; }
        .chat-bubble { max-width: 85%; border-radius: 1rem; padding: .75rem .85rem; line-height: 1.55; white-space: pre-wrap; }
        .chat-bubble.user { margin-left: auto; background: linear-gradient(135deg, var(--green), #0a7a43); color: #fff; }
        .chat-bubble.bot { background: #fff; color: var(--text); border: 1px solid var(--line); }
        .chat-form { display: flex; gap: .6rem; padding: .9rem; border-top: 1px solid var(--line); background: #fff; }
        .chat-form textarea { min-height: 3rem; max-height: 8rem; width: 100%; border: 1px solid var(--line); border-radius: .9rem; padding: .72rem .9rem; font: inherit; }

    </style>
</head>
<body>
    <a class="skip-link" href="#overview">Skip to dashboard content</a>

    @php
        $user = auth()->user();
        $userName = $user->name ?? 'Librarian';
        $search = trim((string) request('search', ''));
        $announcementSearch = trim((string) request('announcement_search', ''));

        $stats = $stats ?? [
            'registered_students' => 1284,
            'registered_instructors' => 146,
            'pending_pickups' => 27,
            'active_overdue' => 19,
        ];

        $bookStatuses = $bookStatuses ?? ['Available', 'Unavailable', 'Archived'];
        $genres = collect($genres);
        $announcements = collect($announcements);
        $announcementAudiences = $announcementAudiences ?? [
            'public' => 'Public / Guest',
            'all' => 'All Roles',
            'librarian' => 'Librarian Only',
            'instructor' => 'Instructor Only',
            'student' => 'Student Only',
            'guest' => 'Guest Only',
        ];
        $announcementStatuses = $announcementStatuses ?? ['Draft', 'Published', 'Archived'];

        $inventory = collect($inventory)->filter(function ($book) use ($search) {
            if ($search === '') {
                return true;
            }

            return str_contains(strtolower(implode(' ', [$book['isbn'], $book['title'], $book['author'], $book['genre']])), strtolower($search));
        })->values();

        $borrowedBooks = collect($borrowedBooks);

        $historyItems = collect($historyItems);

        $loginLogs = collect($loginLogs);
        $activityLogs = collect($activityLogs);

        $topBooksData = collect($topBorrowedBooks)->map(function ($book) {
            return [
                'title' => $book->title,
                'count' => $book->borrow_count,
            ];
        })->values();

        $genreData = collect($genreStats)->map(function ($genre) {
            return [
                'genre' => $genre->genre,
                'count' => $genre->count,
            ];
        })->values();

        $trendData = collect($borrowTrend)->map(function ($row) {
            return [
                'month' => $row->month,
                'count' => $row->borrow_count,
            ];
        })->values();
    @endphp

    <header class="topbar">
        <a class="brand" href="{{ route('home') }}">
            <img src="{{ asset('picture/ISU.jpg') }}" alt="ISU logo">
            <span class="brand-copy">
                <strong>ISU Library System</strong>
                <span>Librarian control center</span>
            </span>
        </a>

        <div class="topbar-meta">
            <span class="pill">Logged in as {{ $userName }}</span>
            <span class="pill">Role: Librarian</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout" type="submit">Log out</button>
            </form>
        </div>
    </header>

    <div class="layout">
        <aside class="sidebar" aria-label="Librarian navigation">
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <img src="{{ asset('picture/ISU.jpg') }}" alt="ISU logo">
                    <div>
                        <h2>Librarian Panel</h2>
                        <p>Manage books, people, circulation, fines, reports, and history from one clean workspace.</p>
                    </div>
                </div>

                <nav class="nav-group">
                    <p class="nav-group-label">Workspace</p>
                    <a class="nav-link active" href="#overview" data-nav-link="overview">
                        <span>Dashboard</span>
                        <span class="nav-badge">{{ number_format((int) $stats['active_overdue']) }}</span>
                    </a>
                    <a class="nav-link" href="#inventory" data-nav-link="inventory">
                        <span>Books</span>
                        <span class="nav-badge">{{ $inventory->count() }}</span>
                    </a>
                    <a class="nav-link" href="#add-book" data-nav-link="add-book">
                        <span>Add Book</span>
                        <span class="nav-badge">New</span>
                    </a>
                    <a class="nav-link" href="#reviews" data-nav-link="reviews">
                        <span>Reviews</span>
                        <span class="nav-badge">{{ $allReviews->count() }}</span>
                    </a>
                    <a class="nav-link" href="#users" data-nav-link="users">
                        <span>Users</span>
                        <span class="nav-badge">{{ $users->count() }}</span>
                    </a>
                    <a class="nav-link" href="#circulation" data-nav-link="circulation">
                        <span>Borrowed Books</span>
                        <span class="nav-badge">{{ $borrowedBooks->count() }}</span>
                    </a>
                    <a class="nav-link" href="#issue-book" data-nav-link="issue-book">
                        <span>Issue Book</span>
                        <span class="nav-badge">New</span>
                    </a>
                    <a class="nav-link" href="#history" data-nav-link="history">
                        <span>Borrow History</span>
                        <span class="nav-badge">{{ $historyItems->count() }}</span>
                    </a>
                    <a class="nav-link" href="#fines" data-nav-link="fines">
                        <span>Fines</span>
                        <span class="nav-badge">{{ $fines->count() }}</span>
                    </a>
                    <a class="nav-link" href="#announcements" data-nav-link="announcements">
                        <span>Announcements</span>
                        <span class="nav-badge">{{ $announcements->count() }}</span>
                    </a>
                    <a class="nav-link" href="#notifications" data-nav-link="notifications">
                        <span>Notifications</span>
                        <span class="nav-badge">{{ $unreadCount }}</span>
                    </a>
                    <a class="nav-link" href="#charts" data-nav-link="charts">
                        <span>Analytics</span>
                        <span class="nav-badge">New</span>
                    </a>
                    <a class="nav-link" href="#reports" data-nav-link="reports">
                        <span>Reports</span>
                        <span class="nav-badge">Export</span>
                    </a>
                    <a class="nav-link" href="#notes" data-nav-link="notes">
                        <span>Activity</span>
                        <span class="nav-badge">{{ $activityLogs->count() }}</span>
                    </a>
                </nav>
            </div>
        </aside>

        <main>
            <div class="shell">
                @if (session('status'))
                    <div class="flash">{{ session('status') }}</div>
                @endif

                <section class="section active" id="overview" data-section="overview">
                    <div class="hero section-anchor">
                        <div>
                            <span class="eyebrow">Librarian dashboard</span>
                            <h1>Welcome, {{ $userName }}.</h1>
                            <p>
                                Keep the collection moving, monitor circulation, and manage student and instructor records from a calm, clean workspace.
                                This dashboard includes the missing librarian features from the reference design, rebuilt in a simpler layout.
                            </p>
                        </div>

                        <div class="hero-actions">
                            <a class="action-link primary" href="#inventory" data-nav-link="inventory">Review inventory</a>
                            <a class="action-link secondary" href="#circulation" data-nav-link="circulation">Check circulation</a>
                            <a class="action-link secondary" href="#history" data-nav-link="history">Open history</a>
                        </div>
                    </div>

                    <section class="status-grid" aria-label="Library overview metrics">
                        <article class="status-card green">
                            <span class="label">Registered Students</span>
                            <strong class="count">{{ number_format((int) $stats['registered_students']) }}</strong>
                            <p>Active student accounts available for borrowing and reservations.</p>
                        </article>
                        <article class="status-card gold">
                            <span class="label">Registered Instructors</span>
                            <strong class="count">{{ number_format((int) $stats['registered_instructors']) }}</strong>
                            <p>Instructor records that can borrow and manage book requests.</p>
                        </article>
                        <article class="status-card orange">
                            <span class="label">Pending Pickups</span>
                            <strong class="count">{{ number_format((int) $stats['pending_pickups']) }}</strong>
                            <p>Reservations waiting for pickup confirmation or staff follow-up.</p>
                        </article>
                        <article class="status-card red">
                            <span class="label">Active / Overdue</span>
                            <strong class="count">{{ number_format((int) $stats['active_overdue']) }}</strong>
                            <p>Borrowed items that need monitoring, renewal, or return action.</p>
                        </article>
                    </section>
                </section>

                <section class="panel section section-anchor" id="inventory" data-section="inventory">
                    <div class="panel-header">
                        <div>
                            <h2>Book Management</h2>
                            <p>Add, filter, and review catalog entries from one form-driven workspace.</p>
                        </div>
                    </div>

                        <form class="toolbar toolbar-filter" method="GET" action="{{ route('dashboard.librarian') }}#inventory">
                            <input name="search" type="search" value="{{ $search }}" placeholder="Search ISBN, title, author, or genre">
                            <select name="genre" aria-label="Filter by genre">
                                <option value="">All genres</option>
                                @foreach ($genres as $genre)
                                <option value="{{ $genre }}" @selected($genreFilter === $genre)>{{ $genre }}</option>
                            @endforeach
                        </select>
                        <select name="status" aria-label="Filter by status">
                            <option value="">All statuses</option>
                            @foreach ($bookStatuses as $statusOption)
                                <option value="{{ $statusOption }}" @selected($statusFilter === $statusOption)>{{ $statusOption }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-green" type="submit">Filter</button>
                        <a class="action-link secondary" href="{{ route('dashboard.librarian') }}#inventory">Reset</a>
                    </form>

                    <div class="panel-subtle">
                        @if ($inventory->isEmpty())
                            <div class="empty-state">No inventory items matched your search.</div>
                        @else
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ISBN</th>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Genre</th>
                                            <th>Year</th>
                                            <th>Qty</th>
                                            <th>Available</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Rating</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($inventory as $book)
                                            @php
                                                $status = strtolower($book['status']);
                                            @endphp
                                            <tr>
                                                <td>{{ $book['isbn'] }}</td>
                                                <td><strong>{{ $book['title'] }}</strong></td>
                                                <td>{{ $book['author'] }}</td>
                                                <td>{{ $book['genre'] }}</td>
                                                <td>{{ $book['year'] ?? 'N/A' }}</td>
                                                <td>{{ $book['qty'] }}</td>
                                                <td>{{ $book['available_quantity'] ?? $book['qty'] }}</td>
                                                <td>{{ $book['location'] ?? 'N/A' }}</td>
                                                <td><span class="badge {{ $status === 'available' ? 'good' : ($status === 'low stock' ? 'warn' : 'danger') }}">{{ $book['status'] }}</span></td>
                                                <td>
                                                    @php $avg = $book['avg_rating'] ?? null; @endphp
                                                    @if ($avg)
                                                        <div class="stars">
                                                            @for ($s = 1; $s <= 5; $s++)
                                                                <span class="star {{ $s <= round($avg) ? '' : 'empty' }}">★</span>
                                                            @endfor
                                                            <span class="rating-value">{{ number_format($avg, 1) }}</span>
                                                            <span class="muted">({{ $book['review_count'] ?? 0 }})</span>
                                                        </div>
                                                    @else
                                                        <span class="muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button class="btn action-link secondary js-edit-book" type="button" data-book="{{ json_encode($book) }}">Edit</button>
                                                    <form method="POST" action="{{ url('/dashboard/librarian/books').'/'.$book['isbn'] }}" style="display:inline-block;" onsubmit="return confirm('Delete this book?');">
                                                     
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="panel section section-anchor" id="announcements" data-section="announcements">
                    <div class="panel-header">
                        <div>
                            <h2>Announcements</h2>
                            <p>Publish and maintain notices for the library community.</p>
                        </div>
                    </div>

                    <form class="toolbar" method="GET" action="{{ route('dashboard.librarian') }}#announcements">
                        <input name="announcement_search" type="search" value="{{ $announcementSearch }}" placeholder="Search title, body, or audience">
                        <select name="audience" aria-label="Filter by audience">
                            <option value="">All audiences</option>
                            @foreach ($announcementAudiences as $value => $label)
                                <option value="{{ $value }}" @selected(($audienceFilter ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-green" type="submit">Filter</button>
                        <a class="action-link secondary" href="{{ route('dashboard.librarian') }}#announcements">Reset</a>
                    </form>

                    <div class="stack-form">
                        <div class="stack-card">
                            <h3>Publish Announcement</h3>
                            <p>Create a new announcement for a specific audience or for everyone.</p>
                            <form method="POST" action="{{ route('librarian.announcements.store') }}">
                                @csrf
                                <div class="form-grid">
                                    <div class="field full">
                                        <label>Title</label>
                                        <input id="announcement-title" name="title" type="text" placeholder="Announcement title" required>
                                    </div>
                                    <div class="field full">
                                        <label>Body</label>
                                        <textarea id="announcement-body" name="body" placeholder="Announcement details" required></textarea>
                                    </div>
                                    <div class="field">
                                        <label>Audience</label>
                                        <select id="announcement-audience" name="audience" required>
                                            @foreach ($announcementAudiences as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Status</label>
                                        <select id="announcement-status" name="status" required>
                                            @foreach ($announcementStatuses as $status)
                                                <option value="{{ $status }}" @selected($status === 'Published')>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="field full">
                                        <label>Published At</label>
                                        <input id="announcement-published-at" name="published_at" type="date" value="{{ now()->toDateString() }}">
                                    </div>
                                </div>

                                <div class="hero-actions">
                                    <button class="btn btn-green" type="submit">Publish Announcement</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="panel-subtle">
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>Title</th><th>Audience</th><th>Status</th><th>Published</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($announcements as $announcement)
                                        @php
                                            $announcementJs = json_encode([
                                                'id' => $announcement->id,
                                                'title' => $announcement->title,
                                                'body' => $announcement->body,
                                                'audience' => $announcement->audience,
                                                'status' => $announcement->status,
                                                'published_at' => $announcement->published_at,
                                            ]);
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $announcement->title }}</strong>
                                                <div class="muted">{{ \Illuminate\Support\Str::limit($announcement->body, 90) }}</div>
                                            </td>
                                            <td>{{ ucfirst($announcement->audience) }}</td>
                                            <td>
                                                <span class="badge {{ $announcement->status === 'Published' ? 'good' : ($announcement->status === 'Draft' ? 'warn' : 'danger') }}">
                                                    {{ $announcement->status }}
                                                </span>
                                            </td>
                                            <td>{{ $announcement->published_at ?? 'N/A' }}</td>
                                            <td>
                                                <div class="actions">
                                                    <button class="btn btn-blue js-edit-announcement" type="button" data-announcement="{{ $announcementJs }}">Edit</button>
                                                    <form method="POST" action="{{ route('librarian.announcements.destroy', $announcement->id) }}">
                                                       
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="muted">No announcements found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section class="panel section section-anchor" id="circulation" data-section="circulation">
                    <div class="panel-header">
                        <div>
                            <h2>Borrowed Books</h2>
                            <p>Track current checkouts and overdue items in one place.</p>
                        </div>
                    </div>

                    <div class="panel-subtle">
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Book Title</th>
                                        <th>Borrowed By</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($borrowedBooks as $item)
                                        <tr>
                                            <td><strong>{{ $item['title'] }}</strong></td>
                                            <td>{{ $item['borrower'] }}</td>
                                            <td>{{ $item['due_date'] }}</td>
                                            <td>
                                                <span class="badge {{ strtolower($item['status']) === 'overdue' ? 'danger' : 'good' }}">{{ $item['status'] }}</span>
                                                <form method="POST" action="{{ route('librarian.loans.return', $item['id']) }}" style="display:inline-block; margin-left:0.5rem;">
                                                    @csrf
                                                    <button class="btn btn-red" type="submit" style="min-height:2.1rem; padding:0.35rem 0.7rem; border-radius:999px;">Return</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="muted">No books are currently borrowed.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section class="panel section section-anchor" id="history" data-section="history">
                    <div class="panel-header">
                        <div>
                            <h2>Borrow History</h2>
                            <p>Review borrowed, returned, and overdue records in a clean table.</p>
                        </div>
                    </div>

                    <div class="panel-subtle">
                        <div class="pill-row">
                            <span class="work-pill">Returned</span>
                            <span class="work-pill">Borrowed</span>
                            <span class="work-pill">Overdue</span>
                        </div>

                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ISBN</th>
                                        <th>Title</th>
                                        <th>Borrower</th>
                                        <th>Borrowed</th>
                                        <th>Returned</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historyItems as $item)
                                        @php
                                            $status = strtolower($item['status']);
                                        @endphp
                                        <tr>
                                            <td>{{ $item['isbn'] }}</td>
                                            <td><strong>{{ $item['title'] }}</strong></td>
                                            <td>{{ $item['borrower'] }}</td>
                                            <td>{{ $item['borrowed_date'] }}</td>
                                            <td>{{ $item['returned_date'] }}</td>
                                            <td><span class="badge {{ $status === 'returned' ? 'good' : ($status === 'overdue' ? 'danger' : 'warn') }}">{{ $item['status'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section class="panel section section-anchor" id="add-book" data-section="add-book">
                    <div class="panel-header">
                        <div>
                            <h2>Add Book</h2>
                            <p>Scan a barcode or type an ISBN to autofill catalog metadata from Google Books.</p>
                        </div>
                    </div>

                    <div class="panel-subtle">
                        <div class="stack-card" style="margin-bottom: 1rem;">
                            <h3>Use Your Phone</h3>
                            <p class="muted" style="margin:0;">Open this link on your phone to scan the barcode and send the ISBN back to this page.</p>
                            <p class="muted" style="margin:.25rem 0 0;">Current host: <strong>{{ request()->getSchemeAndHttpHost() }}</strong></p>
                            <div id="phoneScanQr" style="width: 160px; height: 160px; margin-top: .75rem; padding: .5rem; background: #fff; border-radius: 16px;"></div>
                            <div style="display:flex; gap:.75rem; flex-wrap:wrap; align-items:center; margin-top:.75rem;">
                                <input type="text" readonly value="{{ $scanPhoneUrl ?? '' }}" style="flex:1; min-width: 220px;" aria-label="Phone scan link">
                                <a class="btn btn-gold" href="{{ $scanPhoneUrl ?? '#' }}" target="_blank" rel="noopener">Open on Phone</a>
                            </div>
                            <p class="muted" style="margin:0;">This session stays active for about 15 minutes.</p>
                        </div>

                        <form class="stack-card" id="librarianBookCreateForm" method="POST" action="{{ route('librarian.books.store') }}">
                            @csrf
                            <div class="form-grid">
                                <div class="field full">
                                    <label for="librarian-book-isbn">ISBN</label>
                                    <div style="display:flex; gap:.75rem; flex-wrap:wrap; align-items:center;">
                                        <input id="librarian-book-isbn" name="isbn" type="text" inputmode="numeric" autocomplete="off" placeholder="978-0132350884" required style="flex:1; min-width: 220px;">
                                        <button class="btn btn-green" type="button" id="openLibrarianBarcodeScanner">Scan Barcode</button>
                                    </div>
                                    <p class="muted" style="margin-top:.5rem;">The scanner is tuned for wide EAN-13 / EAN-8 barcodes.</p>
                                </div>
                                <div class="field">
                                    <label for="librarian-book-title">Title</label>
                                    <input id="librarian-book-title" name="title" type="text" placeholder="Book title" required>
                                </div>
                                <div class="field">
                                    <label for="librarian-book-author">Author</label>
                                    <input id="librarian-book-author" name="author" type="text" placeholder="Author name" required>
                                </div>
                                <div class="field">
                                    <label for="librarian-book-genre">Genre</label>
                                    <input id="librarian-book-genre" name="genre" list="genreOptions" type="text" placeholder="Select or type a genre" required>
                                </div>
                                <div class="field">
                                    <label for="librarian-book-year">Year Published</label>
                                    <input id="librarian-book-year" name="year_published" type="number" min="1000" max="9999" placeholder="2024">
                                </div>
                                <div class="field">
                                    <label for="librarian-book-quantity">Quantity</label>
                                    <input id="librarian-book-quantity" name="quantity" type="number" min="1" value="1" required>
                                </div>
                                <div class="field">
                                    <label for="librarian-book-location">Location</label>
                                    <input id="librarian-book-location" name="location" type="text" placeholder="Shelf A1, Floor 2">
                                </div>
                                <div class="field full">
                                    <label for="librarian-book-description">Description</label>
                                    <textarea id="librarian-book-description" name="description" placeholder="Optional book description"></textarea>
                                </div>
                            </div>

                            <div class="hero-actions">
                                <button class="btn btn-green" type="submit">Save Book</button>
                            </div>
                        </form>
                    </div>
                </section>

                <div class="modal-backdrop" id="librarianBarcodeScannerModal" aria-hidden="true">
                    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="librarianBarcodeScannerTitle" style="max-width: 720px;">
                <div class="modal-header">
                    <div>
                        <h3 id="librarianBarcodeScannerTitle">Scan Barcode</h3>
                        <p class="muted">Point the camera at an ISBN barcode. The scanner will close automatically after a match.</p>
                    </div>
                    <button class="modal-close" type="button" id="closeLibrarianBarcodeScanner">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="librarianBarcodeReader" style="width:100%; min-height: 320px; border: 1px dashed rgba(255,255,255,.18); border-radius: 16px; overflow: hidden; background: rgba(255,255,255,.03);"></div>
                    <p id="librarianBarcodeScannerStatus" class="muted" style="margin-top: .75rem;">Camera access is required. We’ll stop scanning as soon as a code is read.</p>
                    <div id="librarianBarcodeHelp" class="stack-card" style="margin-top: .75rem; display:none;">
                        <h4 style="margin:0;">If your camera is blocked on iPhone</h4>
                        <p class="muted" style="margin:0;">
                            Open Safari settings, allow camera access for this site, then tap Retry Camera.
                            On iPhone, the first camera prompt usually appears only after you press the scan button.
                        </p>
                    </div>
                    <div class="hero-actions" style="margin-top: .75rem;">
                        <button class="btn btn-green" type="button" id="retryLibrarianBarcodeScanner">Retry Camera</button>
                    </div>
                </div>
            </div>
        </div>

                <section class="panel section section-anchor" id="issue-book" data-section="issue-book">
                    <div class="panel-header">
                        <div>
                            <h2>Issue Book</h2>
                            <p>Issue a book manually at the top, then process the reservation queue below.</p>
                        </div>
                    </div>

                    <div class="panel-subtle">
                        <form class="stack-card" method="POST" action="{{ route('librarian.books.issue', 'ISBN-HERE') }}" onsubmit="this.action = this.action.replace('ISBN-HERE', encodeURIComponent(this.querySelector('[name=isbn]').value));">
                            @csrf
                            <h3>Manual Issue</h3>
                            <p>Use this when the borrower is not coming from the reservation queue.</p>
                            <div class="form-grid">
                            <div class="field">
                                    <label for="issue-isbn">ISBN</label>
                                    <input id="issue-isbn" name="isbn" type="text" placeholder="978-0132350884" required>
                                </div>
                                <div class="field">
                                    <label for="issue-user">Borrower User ID or Login ID</label>
                                    <input id="issue-user" name="user_id" type="text" placeholder="Borrower ID or login ID" required>
                                </div>
                                <div class="field">
                                    <label for="issue-quantity">Quantity</label>
                                    <input id="issue-quantity" name="quantity" type="number" min="1" max="10" value="1" required>
                                </div>
                            </div>
                            <div class="hero-actions">
                                <button class="btn btn-green" type="submit">Issue Manually</button>
                            </div>
                        </form>

                        <div style="height: 1rem;"></div>

                        <div class="stack-card">
                            <h3>Reservation Queue</h3>
                            
                            @if (empty($reservations) || $reservations->isEmpty())
                                <div class="empty-state">No active reservations are waiting for issue right now.</div>
                            @else
                                <div class="table-wrap">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Book</th>
                                                <th>Reserved By</th>
                                                <th>Student ID</th>
                                                <th>Reserved At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($reservations as $reservation)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $reservation['title'] }}</strong>
                                                        <div class="muted">{{ $reservation['isbn'] }}</div>
                                                    </td>
                                                    <td>{{ $reservation['borrower_name'] }}</td>
                                                    <td>{{ $reservation['borrower_id'] }}</td>
                                                    <td>{{ $reservation['reserved_at'] }}</td>
                                                    <td>
                                                        <div class="hero-actions" style="margin-top:0; gap:0.5rem;">
                                                            <form method="POST" action="{{ route('librarian.reservations.issue', $reservation['id']) }}">
                                                                @csrf
                                                                <button class="btn btn-green" type="submit">Issue</button>
                                                            </form>
                                                            <form method="POST" action="{{ route('librarian.reservations.decline', $reservation['id']) }}">
                                                                @csrf
                                                                <button class="btn btn-red" type="submit">Decline</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="panel section section-anchor" id="users" data-section="users">
                    <div class="panel-header">
                        <div>
                            <h2>Users</h2>
                            
                        </div>
                    </div>

                    <form class="toolbar" method="GET" action="{{ route('dashboard.librarian') }}#users">
                        <input type="text" name="user_search" placeholder="Search name, email, or login ID" value="{{ $userSearch }}">
                        <select name="user_role">
                            <option value="">All roles</option>
                            @foreach (['librarian', 'instructor', 'student', 'guest'] as $roleOption)
                                <option value="{{ $roleOption }}" @selected($userRoleFilter === $roleOption)>{{ ucfirst($roleOption) }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-blue" type="submit">Filter</button>
                        <a class="action-link secondary" href="{{ route('dashboard.librarian') }}#users">Reset</a>
                    </form>

                    <div class="stack-card" style="margin-top:1rem; margin-bottom:1rem;">
                        <h3>Pending Student Approvals</h3>
                       
                        @php $pendingStudents = $users->where('role', 'student')->where('status', '!=', 'active'); @endphp
                        @if ($pendingStudents->isNotEmpty())
                            <ul style="margin:.6rem 0 0 1.1rem;">
                                @foreach ($pendingStudents as $pendingStudent)
                                    <li><strong>{{ $pendingStudent->name }}</strong> — {{ $pendingStudent->email }} ({{ ucfirst($pendingStudent->status ?? 'inactive') }})</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="muted" style="margin:.6rem 0 0;">No pending student approvals right now.</p>
                        @endif
                    </div>

                    <div class="table-wrap" style="margin-top: 1rem;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile Number</th>
                                    <th>Student ID</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $userRow)
                                    <tr>
                                        <td>{{ $userRow->name }}</td>
                                        <td>{{ $userRow->email }}</td>
                                        <td>{{ $userRow->mobile_number ?? 'N/A' }}</td>
                                        <td>{{ $userRow->login_id ?? 'N/A' }}</td>
                                        <td>{{ ucfirst($userRow->role) }}</td>
                                        <td><span class="badge {{ ($userRow->status ?? 'active') === 'active' ? 'good' : 'danger' }}">{{ ucfirst($userRow->status ?? 'active') }}</span></td>
                                        <td>
                                            @if ($userRow->id !== auth()->id())
                                                <form method="POST" action="{{ route('librarian.users.status', $userRow->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn {{ ($userRow->status ?? 'active') === 'active' ? 'btn-red' : 'btn-green' }}" type="submit">
                                                        {{ ($userRow->status ?? 'active') === 'active' ? 'Deactivate' : (($userRow->role ?? '') === 'student' && ($userRow->status ?? 'active') !== 'active' ? 'Approve' : 'Activate') }}
                                                    </button>
                                                </form>
                                            @else
                                                <span class="muted">Current account</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="muted">No users found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="stack-card" style="margin-top: 1.5rem;">
                        <h3>Add Staff Account</h3>
                        <p>Create a librarian, instructor, or student account.</p>
                        <form method="POST" action="{{ route('librarian.users.store') }}" class="form-grid" style="margin-top: 0.75rem;">
                            @csrf
                            <div class="field">
                                <label for="user-create-name">Name</label>
                                <input id="user-create-name" name="name" type="text" required>
                            </div>
                            <div class="field">
                                <label for="user-create-email">Email</label>
                                <input id="user-create-email" name="email" type="email" required>
                            </div>
                            <div class="field">
                                <label for="user-create-mobile-number">Mobile Number</label>
                                <input id="user-create-mobile-number" name="mobile_number" type="text" required placeholder="e.g. 09171234567">
                            </div>
                            <div class="field">
                                <label for="user-create-login-id">Login ID</label>
                                <input id="user-create-login-id" name="login_id" type="text">
                            </div>
                            <div class="field">
                                <label for="user-create-role">Role</label>
                                <select id="user-create-role" name="role" required>
                                    <option value="librarian">Librarian</option>
                                    <option value="instructor">Instructor</option>
                                    <option value="student">Student</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="user-create-password">Password</label>
                                <input id="user-create-password" name="password" type="password" minlength="6" required>
                                <div class="strength-meter" aria-live="polite">
                                    <div class="strength-bar"><div id="user-create-strength-fill" class="strength-fill"></div></div>
                                    <div id="user-create-strength-text" class="strength-text">Enter a password</div>
                                </div>
                            </div>
                            <div class="field full">
                                <button class="btn btn-green" type="submit">Create Account</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="panel section section-anchor" id="fines" data-section="fines">
                    <div class="panel-header">
                        <div>
                            <h2>Fines</h2>
                            <p>Review, recalculate, and settle overdue fines.</p>
                        </div>
                        <div class="toolbar-actions">
                            <form method="POST" action="{{ route('librarian.fines.recalculate') }}">
                                @csrf
                                <button class="btn btn-blue" type="submit">Recalculate Fines</button>
                            </form>
                            <a class="btn action-link secondary" href="{{ route('librarian.fines.export', ['format' => 'csv']) }}">Export CSV</a>
                        </div>
                    </div>

                    <form class="toolbar" method="GET" action="{{ route('dashboard.librarian') }}#fines">
                        <select name="fine_status">
                            <option value="">All statuses</option>
                            <option value="Unpaid" @selected($fineStatusFilter === 'Unpaid')>Unpaid</option>
                            <option value="Paid" @selected($fineStatusFilter === 'Paid')>Paid</option>
                            <option value="Waived" @selected($fineStatusFilter === 'Waived')>Waived</option>
                        </select>
                        <button class="btn btn-blue" type="submit">Filter</button>
                        <a class="action-link secondary" href="{{ route('dashboard.librarian') }}#fines">Reset</a>
                    </form>

                    <div class="table-wrap" style="margin-top: 1rem;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Borrower</th>
                                    <th>Book</th>
                                    <th>Overdue Days</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fines as $fine)
                                    <tr>
                                        <td>{{ $fine->borrower_name }}</td>
                                        <td>{{ $fine->title }}</td>
                                        <td>{{ $fine->overdue_days }}</td>
                                        <td>₱{{ number_format($fine->amount, 2) }}</td>
                                        <td><span class="badge {{ $fine->status === 'Unpaid' ? 'danger' : 'good' }}">{{ $fine->status }}</span></td>
                                        <td>
                                            @if ($fine->status === 'Unpaid')
                                                <form method="POST" action="{{ route('librarian.fines.pay', $fine->id) }}" style="display:inline-block;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-green" type="submit">Mark Paid</button>
                                                </form>
                                                <form method="POST" action="{{ route('librarian.fines.waive', $fine->id) }}" style="display:inline-block;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-blue" type="submit">Waive</button>
                                                </form>
                                            @else
                                                <span class="muted">Settled</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="muted">No fines recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="panel section section-anchor" id="reviews" data-section="reviews">
                    <div class="panel-header">
                        <div>
                            <h2>Reviews</h2>
                            <p>Reader feedback and ratings across the catalog.</p>
                        </div>
                        <div class="toolbar-actions">
                            <a class="btn action-link secondary" href="{{ route('librarian.reviews.export', ['format' => 'csv']) }}">Export CSV</a>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Reviewer</th>
                                    <th>Book</th>
                                    <th>Rating</th>
                                    <th>Review</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($allReviews as $review)
                                    <tr>
                                        <td>{{ $review->reviewer_name }}</td>
                                        <td>{{ $review->title }}</td>
                                        <td>{{ $review->rating }}/5</td>
                                        <td>{{ $review->review }}</td>
                                        <td>{{ $review->created_at }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="muted">No reviews yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="panel section section-anchor" id="charts" data-section="charts">
                    <div class="panel-header">
                        <div>
                            <h2>Analytics</h2>
                            <p>Borrowing trends and genre breakdown for the selected month.</p>
                        </div>
                    </div>

                    <form class="toolbar" method="GET" action="{{ route('dashboard.librarian') }}#charts">
                        <select name="month">
                            @foreach ($chartMonths as $month)
                                <option value="{{ $month['value'] }}" @selected($chartMonth === $month['value'])>{{ $month['label'] }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-blue" type="submit">View Month</button>
                        <a class="action-link secondary" href="{{ route('librarian.reports.export', ['type' => 'charts', 'month' => $chartMonth]) }}">Export CSV</a>
                    </form>

                    <div class="cards-2" style="margin-top: 1rem;">
                        <article class="stack-card">
                            <h3>Top Borrowed Books — {{ $chartMonthLabel }}</h3>
                            <div class="table-wrap" style="margin-top: 1rem;">
                                <table>
                                    <thead><tr><th>#</th><th>Book</th><th>Genre</th><th>Borrows</th></tr></thead>
                                    <tbody>
                                        @forelse ($topBorrowedBooks as $index => $book)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $book->title }}</td>
                                                <td>{{ $book->genre }}</td>
                                                <td>{{ $book->borrow_count }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="muted">No borrow activity for this month.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </article>

                        <article class="stack-card">
                            <h3>Catalog by Genre</h3>
                            <div class="table-wrap" style="margin-top: 1rem;">
                                <table>
                                    <thead><tr><th>Genre</th><th>Books</th></tr></thead>
                                    <tbody>
                                        @forelse ($genreStats as $genre)
                                            <tr><td>{{ $genre->genre }}</td><td>{{ $genre->count }}</td></tr>
                                        @empty
                                            <tr><td colspan="2" class="muted">No genre data yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    </div>

                    <div class="cards-3" style="margin-top: 1rem; gap:1rem;">
                        <article class="stack-card">
                            <h3>Top Borrowed Books — Chart</h3>
                            <div style="margin-top:1rem;">
                                <canvas id="chartTopBooks" style="width:100%;height:320px;"></canvas>
                            </div>
                        </article>

                        <article class="stack-card">
                            <h3>Catalog by Genre — Chart</h3>
                            <div style="margin-top:1rem;">
                                <canvas id="chartGenres" style="width:100%;height:320px;"></canvas>
                            </div>
                        </article>

                        <article class="stack-card">
                            <h3>6-Month Borrow Trend — Chart</h3>
                            <div style="margin-top:1rem;">
                                <canvas id="chartTrend" style="width:100%;height:320px;"></canvas>
                            </div>
                        </article>
                    </div>

                    <article class="stack-card" style="margin-top: 1.5rem;">
                        <h3>6-Month Borrow Trend</h3>
                        <div class="table-wrap" style="margin-top: 1rem;">
                            <table>
                                <thead><tr><th>Month</th><th>Borrows</th></tr></thead>
                                <tbody>
                                    @forelse ($borrowTrend as $row)
                                        <tr><td>{{ $row->month }}</td><td>{{ $row->borrow_count }}</td></tr>
                                    @empty
                                        <tr><td colspan="2" class="muted">No trend data yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </article>
                </section>

                <section class="panel section section-anchor" id="reports" data-section="reports">
                    <div class="panel-header">
                        <div>
                            <h2>Reports</h2>
                            <p>Export circulation data as CSV.</p>
                        </div>
                    </div>

                    <div class="cards-2">
                        <article class="stack-card">
                            <h3>Active Loans</h3>
                            <p>Currently borrowed books, including overdue items.</p>
                            <a class="btn action-link secondary" href="{{ route('librarian.reports.export', ['type' => 'loans']) }}">Export CSV</a>
                        </article>
                        <article class="stack-card">
                            <h3>Reservations</h3>
                            <p>Books currently reserved and awaiting pickup.</p>
                            <a class="btn action-link secondary" href="{{ route('librarian.reports.export', ['type' => 'reservations']) }}">Export CSV</a>
                        </article>
                        <article class="stack-card">
                            <h3>Borrow History</h3>
                            <p>Full history of borrowed and returned books.</p>
                            <a class="btn action-link secondary" href="{{ route('librarian.reports.export', ['type' => 'history']) }}">Export CSV</a>
                        </article>
                    </div>
                </section>

                <section class="panel section section-anchor" id="notifications" data-section="notifications">
                    <div class="panel-header">
                        <div>
                            <h2>Notifications</h2>
                            <p>System alerts for reservations, fines, and announcements.</p>
                        </div>
                        <div class="toolbar-actions">
                            <form method="POST" action="{{ route('librarian.notifications.read') }}">
                                @csrf
                                <button class="btn btn-blue" type="submit">Mark All Read</button>
                            </form>
                        </div>
                    </div>

                    @if ($notifications->isEmpty())
                        <div class="empty-state">No notifications yet.</div>
                    @else
                        <div class="mini-list">
                            @foreach ($notifications as $notif)
                                <article class="mini-card {{ $notif->read_at ? '' : 'mini-card--unread' }}">
                                    <strong>{{ $notif->title }}</strong>
                                    <p>{{ $notif->body }}</p>
                                    <p class="muted" style="margin-top:0.5rem;">{{ $notif->created_at }}</p>
                                    @if (! $notif->read_at)
                                        <form method="POST" action="{{ route('librarian.notifications.read.one', $notif->id) }}" style="margin-top:0.5rem;">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-blue" type="submit">Mark Read</button>
                                        </form>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="panel section section-anchor" id="notes" data-section="notes">
                    <div class="panel-header">
                        <div>
                            <h2>Activity</h2>

                            <p>Recent actions and login events across the library system.</p>
                        </div>
                    </div>

                    <div class="cards-2 activity-stack">
                        <article class="stack-card">
                            <h3>Recent Logins</h3>
                            <p>The latest login events captured by the system.</p>
                            <div class="table-wrap" style="margin-top: 1rem;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Role</th>
                                            <th>Action</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($loginLogs as $log)
                                            @php $loginDetails = json_decode($log->details ?? '{}', true) ?: []; @endphp
                                            <tr>
                                                <td><strong>{{ $log->user_name ?? 'System' }}</strong></td>
                                                <td>{{ $loginDetails['role'] ?? 'N/A' }}</td>
                                                <td><span class="badge good">{{ ucfirst($log->action) }}</span></td>
                                                <td>{{ $log->created_at }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="muted">No login events recorded yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </article>

                        <article class="stack-card">
                            <h3>Activity Log</h3>
                            <p>Recent library actions recorded by the system.</p>
                            <div class="table-wrap" style="margin-top: 1rem;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Action</th>
                                            <th>User</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($activityLogs as $log)
                                            <tr>
                                                <td>{{ $log->description ?? ucfirst(str_replace('_', ' ', $log->action)) }}</td>
                                                <td>{{ $log->user_name ?? 'System' }}</td>
                                                <td>{{ $log->created_at }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="muted">No activity recorded yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <div class="modal-backdrop" id="announcementModal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="announcementModalTitle">
            <div class="modal-header">
                <div>
                    <h3 id="announcementModalTitle">Edit Announcement</h3>
                    <p>Update the title, audience, or publish state.</p>
                </div>
                <button class="modal-close" type="button" onclick="closeAnnouncementModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="announcementModalForm">
                    @csrf
                    @method('PATCH')
                    <div class="form-grid">
                        <div class="field full">
                            <label for="announcement-edit-title">Title</label>
                            <input id="announcement-edit-title" name="title" type="text" required>
                        </div>
                        <div class="field full">
                            <label for="announcement-edit-body">Body</label>
                            <textarea id="announcement-edit-body" name="body" required></textarea>
                        </div>
                        <div class="field">
                            <label for="announcement-edit-audience">Audience</label>
                            <select id="announcement-edit-audience" name="audience" required>
                                @foreach ($announcementAudiences as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="announcement-edit-status">Status</label>
                            <select id="announcement-edit-status" name="status" required>
                                @foreach ($announcementStatuses as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field full">
                            <label for="announcement-edit-published-at">Published At</label>
                            <input id="announcement-edit-published-at" name="published_at" type="date">
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-ghost" type="button" onclick="closeAnnouncementModal()">Cancel</button>
                        <button class="btn btn-green" type="submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="editBookModal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="editBookModalTitle">
            <div class="modal-header">
                <div>
                    <h3 id="editBookModalTitle">Edit Book</h3>
                    <p>Update catalog details for this title.</p>
                </div>
                <button class="modal-close" type="button" onclick="closeEditBookModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="editBookModalForm">
                    @csrf
                    @method('PATCH')
                    <div class="form-grid">
                        <div class="field full">
                            <label for="book-edit-title">Title</label>
                            <input id="book-edit-title" name="title" type="text" required>
                        </div>
                        <div class="field">
                            <label for="book-edit-author">Author</label>
                            <input id="book-edit-author" name="author" type="text" required>
                        </div>
                        <div class="field">
                            <label for="book-edit-genre">Genre</label>
                            <input id="book-edit-genre" name="genre" type="text" required>
                        </div>
                        <div class="field">
                            <label for="book-edit-year">Year Published</label>
                            <input id="book-edit-year" name="year_published" type="number" min="1000" max="9999">
                        </div>
                        <div class="field">
                            <label for="book-edit-quantity">Quantity</label>
                            <input id="book-edit-quantity" name="quantity" type="number" min="0" required>
                        </div>
                        <div class="field">
                            <label for="book-edit-available">Available Quantity</label>
                            <input id="book-edit-available" name="available_quantity" type="number" min="0">
                        </div>
                        <div class="field">
                            <label for="book-edit-location">Location</label>
                            <input id="book-edit-location" name="location" type="text">
                        </div>
                        <div class="field">
                            <label for="book-edit-status">Status</label>
                            <select id="book-edit-status" name="status" required>
                                <option value="Available">Available</option>
                                <option value="Unavailable">Unavailable</option>
                                <option value="Archived">Archived</option>
                            </select>
                        </div>
                        <div class="field full">
                            <label for="book-edit-description">Description</label>
                            <textarea id="book-edit-description" name="description"></textarea>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-ghost" type="button" onclick="closeEditBookModal()">Cancel</button>
                        <button class="btn btn-green" type="submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <button class="chat-launcher" type="button" id="chatLauncher">Chat with AI</button>
    <div class="chat-panel" id="chatPanel" aria-hidden="true">
        <div class="chat-head">
            <div>
                <h3>AI Library Assistant</h3>
                <p>Ask about books, users, fines, reports, or dashboard actions.</p>
            </div>
            <button class="chat-close" type="button" id="chatClose">&times;</button>
        </div>
        <div class="chat-log" id="chatLog">
            <div class="chat-bubble bot">Hi, I&rsquo;m your library assistant. Ask me anything about the system.</div>
        </div>
        <form class="chat-form" id="chatForm">
            <textarea id="chatInput" placeholder="Type your question..." required></textarea>
            <button class="logout" type="button" id="chatNew" style="background: rgba(255,255,255,.92); color: var(--green-deep); border: 1px solid var(--line);">New Chat</button>
            <button class="logout" style="background: var(--green); border-color: transparent;" type="submit" id="chatSend">Send</button>
        </form>
    </div>

    <script id="librarian-dashboard-data" type="application/json">{!! json_encode([
        'scanToken' => $scanToken ?? null,
        'scanPhoneUrl' => $scanPhoneUrl ?? null,
        'topBooks' => $topBooksData ?? [],
        'genres' => $genreData ?? [],
        'trend' => $trendData ?? [],
    ]) !!}</script>
    <script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2@1.8.2/dist/quagga.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        (() => {
            const sections = Array.from(document.querySelectorAll("[data-section]"));
            const navLinks = Array.from(document.querySelectorAll("[data-nav-link]"));
            const groups = Array.from(document.querySelectorAll("[data-group]"));
            const announcementModal = document.getElementById("announcementModal");
            const announcementModalForm = document.getElementById("announcementModalForm");
            const editBookModal = document.getElementById("editBookModal");
            const editBookModalForm = document.getElementById("editBookModalForm");
            const toggles = Array.from(document.querySelectorAll("[data-group-toggle]"));
            const librarianBookForm = document.getElementById('librarianBookCreateForm');
            const librarianBookIsbn = document.getElementById('librarian-book-isbn');
            const librarianBarcodeModal = document.getElementById('librarianBarcodeScannerModal');
            const librarianBarcodeReaderId = 'librarianBarcodeReader';
            const librarianBarcodeStatus = document.getElementById('librarianBarcodeScannerStatus');
            const openLibrarianBarcodeScanner = document.getElementById('openLibrarianBarcodeScanner');
            const closeLibrarianBarcodeScanner = document.getElementById('closeLibrarianBarcodeScanner');
            const retryLibrarianBarcodeScanner = document.getElementById('retryLibrarianBarcodeScanner');
            const librarianBarcodeHelp = document.getElementById('librarianBarcodeHelp');
            const phoneScanQr = document.getElementById('phoneScanQr');
            const dashboardDataElement = document.getElementById('librarian-dashboard-data');
            const dashboardData = dashboardDataElement ? JSON.parse(dashboardDataElement.textContent || '{}') : {};
            const scanToken = dashboardData.scanToken;
            const scanPhoneUrl = dashboardData.scanPhoneUrl;
            let librarianBarcodeScannerRunning = false;
            let librarianBarcodeScannerStopping = false;
            let librarianBarcodeScannerLastError = '';
            let librarianBarcodeDetectedHandler = null;
            let scanPollTimer = null;

            const sectionMap = {
                overview: "overview",
                inventory: "inventory",
                "add-book": "add-book",
                announcements: "announcements",
                notes: "notes",
                circulation: "circulation",
                "issue-book": "issue-book",
                history: "history",
                users: "users",
                fines: "fines",
                reviews: "reviews",
                charts: "charts",
                reports: "reports",
                notifications: "notifications",
            };

            const groupMap = {
                overview: "workspace",
                inventory: "workspace",
                "add-book": "workspace",
                announcements: "workspace",
                notes: "workspace",
                circulation: "workspace",
                "issue-book": "workspace",
                history: "workspace",
                users: "workspace",
                fines: "workspace",
                reviews: "workspace",
                charts: "workspace",
                reports: "workspace",
                notifications: "workspace",
            };

            const setAnnouncementField = (selector, value) => {
                if (!announcementModalForm) {
                    return;
                }

                const field = announcementModalForm.querySelector(selector);
                if (field) {
                    field.value = value ?? "";
                }
            };

            const openAnnouncementModal = (announcement) => {
                if (!announcementModal || !announcementModalForm) {
                    return;
                }

                announcementModalForm.action = `{{ url('/dashboard/librarian/announcements') }}/${encodeURIComponent(announcement.id)}`;
                setAnnouncementField('[name="title"]', announcement.title);
                setAnnouncementField('[name="body"]', announcement.body);
                setAnnouncementField('[name="audience"]', announcement.audience ?? 'public');
                setAnnouncementField('[name="status"]', announcement.status ?? 'Published');
                setAnnouncementField('[name="published_at"]', (announcement.published_at ?? '').slice(0, 10));
                announcementModal.classList.add("open");
                announcementModal.setAttribute("aria-hidden", "false");
            };

            window.closeAnnouncementModal = () => {
                if (!announcementModal || !announcementModalForm) {
                    return;
                }

                announcementModal.classList.remove("open");
                announcementModal.setAttribute("aria-hidden", "true");
                announcementModalForm.action = "";
            };

            const setEditBookField = (selector, value) => {
                if (!editBookModalForm) {
                    return;
                }

                const field = editBookModalForm.querySelector(selector);
                if (field) {
                    field.value = value ?? "";
                }
            };

            const openEditBookModal = (book) => {
                if (!editBookModal || !editBookModalForm) {
                    return;
                }

                editBookModalForm.action = `{{ url('/dashboard/librarian/books') }}/${encodeURIComponent(book.isbn)}`;
                setEditBookField('[name="title"]', book.title);
                setEditBookField('[name="author"]', book.author);
                setEditBookField('[name="genre"]', book.genre);
                setEditBookField('[name="year_published"]', book.year);
                setEditBookField('[name="quantity"]', book.qty);
                setEditBookField('[name="available_quantity"]', book.available_quantity ?? book.qty);
                setEditBookField('[name="location"]', book.location);
                setEditBookField('[name="status"]', book.status ?? 'Available');
                setEditBookField('[name="description"]', book.description);
                editBookModal.classList.add("open");
                editBookModal.setAttribute("aria-hidden", "false");
            };

            window.closeEditBookModal = () => {
                if (!editBookModal || !editBookModalForm) {
                    return;
                }

                editBookModal.classList.remove("open");
                editBookModal.setAttribute("aria-hidden", "true");
                editBookModalForm.action = "";
            };

            const normalizeIsbn = (value) => (value || "").replace(/[^0-9Xx]/g, "").toUpperCase();

            const fetchBookLookup = async (isbn) => {
                const response = await fetch(`{{ url('/books/lookup') }}/${encodeURIComponent(isbn)}`, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    return null;
                }

                return await response.json();
            };

            const fillLibrarianBookForm = (data = {}) => {
                if (!librarianBookForm) {
                    return;
                }

                const mappings = {
                    isbn: data.isbn,
                    title: data.title,
                    author: data.author,
                    genre: data.genre,
                    year_published: data.year_published,
                };

                Object.entries(mappings).forEach(([name, value]) => {
                    const input = librarianBookForm.querySelector(`[name="${name}"]`);
                    if (!input || value === undefined || value === null || value === '') {
                        return;
                    }

                    input.value = value;
                });

                const quantityInput = librarianBookForm.querySelector('[name="quantity"]');
                if (quantityInput && !quantityInput.value) {
                    quantityInput.value = 1;
                }
            };

            const lookupAndFillLibrarianBook = async (isbn) => {
                const normalized = normalizeIsbn(isbn);
                if (normalized.length < 8) {
                    return;
                }

                if (librarianBookIsbn) {
                    librarianBookIsbn.value = normalized;
                }

                const metadata = await fetchBookLookup(normalized);
                if (!metadata) {
                    return;
                }

                fillLibrarianBookForm(metadata);
            };

            const startScanPolling = () => {
                if (!scanToken || scanPollTimer) {
                    return;
                }

                scanPollTimer = window.setInterval(async () => {
                    try {
                        const response = await fetch(`{{ url('/scan') }}/${encodeURIComponent(scanToken)}/status`, {
                            headers: { Accept: 'application/json' },
                        });

                        if (!response.ok) {
                            return;
                        }

                        const data = await response.json();
                        if (data.status !== 'scanned' || !data.isbn) {
                            return;
                        }

                        clearInterval(scanPollTimer);
                        scanPollTimer = null;
                        setLibrarianScannerStatus(`Phone scan received: ${data.isbn}. Fetching book data...`);
                        await lookupAndFillLibrarianBook(data.isbn);
                    } catch (error) {
                        console.warn('Phone scan polling failed', error);
                    }
                }, 2000);
            };

            startScanPolling();

            if (phoneScanQr && scanPhoneUrl && window.QRCode) {
                phoneScanQr.innerHTML = '';
                new QRCode(phoneScanQr, {
                    text: scanPhoneUrl,
                    width: 144,
                    height: 144,
                    colorDark: '#0f172a',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M,
                });
            }

            librarianBookIsbn?.addEventListener('input', async () => {
                const normalized = normalizeIsbn(librarianBookIsbn.value);
                if (normalized.length < 8) {
                    return;
                }

                await lookupAndFillLibrarianBook(normalized);
            });

            const closeLibrarianBarcodeScannerModal = async () => {
                librarianBarcodeScannerStopping = true;
                librarianBarcodeScannerLastError = '';

                if (scanPollTimer) {
                    clearInterval(scanPollTimer);
                    scanPollTimer = null;
                }

                try {
                    if (librarianBarcodeScannerRunning && window.Quagga) {
                        if (librarianBarcodeDetectedHandler) {
                            Quagga.offDetected(librarianBarcodeDetectedHandler);
                        }
                        Quagga.stop();
                    }
                } catch (error) {
                    console.warn('Librarian barcode scanner stop failed', error);
                } finally {
                    librarianBarcodeScannerRunning = false;
                    librarianBarcodeDetectedHandler = null;
                    librarianBarcodeScannerStopping = false;
                    const readerEl = document.getElementById(librarianBarcodeReaderId);
                    if (readerEl) {
                        readerEl.innerHTML = '';
                    }
                    if (librarianBarcodeHelp) {
                        librarianBarcodeHelp.style.display = 'none';
                    }
                    if (librarianBarcodeModal) {
                        librarianBarcodeModal.classList.remove('open');
                        librarianBarcodeModal.setAttribute('aria-hidden', 'true');
                    }
                }
            };

            const setLibrarianScannerStatus = (message, isError = false) => {
                if (!librarianBarcodeStatus) {
                    return;
                }

                librarianBarcodeStatus.textContent = message;
                librarianBarcodeStatus.style.color = isError ? '#fca5a5' : '';
                if (librarianBarcodeHelp) {
                    librarianBarcodeHelp.style.display = isError ? 'block' : 'none';
                }
            };

            const startLibrarianBarcodeScanner = async () => {
                if (!window.Quagga || !librarianBarcodeModal || !librarianBarcodeStatus) {
                    setLibrarianScannerStatus('Barcode scanner library failed to load. Please refresh and try again.', true);
                    return;
                }

                librarianBarcodeModal.classList.add('open');
                librarianBarcodeModal.setAttribute('aria-hidden', 'false');
                setLibrarianScannerStatus('Starting camera...');
                startScanPolling();

                if (librarianBarcodeScannerRunning) {
                    await closeLibrarianBarcodeScannerModal();
                }

                const readerEl = document.getElementById(librarianBarcodeReaderId);
                if (readerEl) {
                    readerEl.innerHTML = '';
                }

                let detectionHandled = false;

                Quagga.init(
                    {
                        inputStream: {
                            name: 'Live',
                            type: 'LiveStream',
                            target: readerEl,
                            constraints: {
                                width: { ideal: 1280 },
                                height: { ideal: 720 },
                                facingMode: { ideal: 'environment' },
                            },
                        },
                        locator: {
                            patchSize: 'medium',
                            halfSample: true,
                        },
                        locate: true,
                        decoder: {
                            readers: [
                                'ean_reader',
                                'ean_8_reader',
                                'upc_reader',
                                'upc_e_reader',
                                'code_128_reader',
                                'code_39_reader',
                                'codabar_reader',
                            ],
                        },
                    },
                    (initError) => {
                        if (initError) {
                            librarianBarcodeScannerLastError = String(initError);
                            console.warn('Quagga init failed', initError);

                            if (/permission|notallowed|denied|camera|video/i.test(String(initError))) {
                                setLibrarianScannerStatus('Camera permission was denied. Allow camera access in your browser settings, then tap Retry Camera.', true);
                                return;
                            }

                            setLibrarianScannerStatus('Could not start the camera. Tap Retry Camera to try again.', true);
                            return;
                        }

                        librarianBarcodeScannerRunning = true;
                        Quagga.start();
                        setLibrarianScannerStatus('Point the camera at a barcode and hold steady.');

                        librarianBarcodeDetectedHandler = async (result) => {
                            if (detectionHandled || librarianBarcodeScannerStopping) {
                                return;
                            }

                            const isbn = normalizeIsbn(result?.codeResult?.code);
                            if (!isbn) {
                                return;
                            }

                            detectionHandled = true;
                            setLibrarianScannerStatus(`Barcode detected: ${isbn}. Fetching book data...`);
                            await lookupAndFillLibrarianBook(isbn);
                            await closeLibrarianBarcodeScannerModal();
                        };

                        Quagga.onDetected(librarianBarcodeDetectedHandler);
                    }
                );
            };

            openLibrarianBarcodeScanner?.addEventListener('click', startLibrarianBarcodeScanner);
            closeLibrarianBarcodeScanner?.addEventListener('click', closeLibrarianBarcodeScannerModal);
            retryLibrarianBarcodeScanner?.addEventListener('click', async () => {
                await closeLibrarianBarcodeScannerModal();
                await startLibrarianBarcodeScanner();
            });

            librarianBarcodeModal?.addEventListener('click', (event) => {
                if (event.target === librarianBarcodeModal) {
                    closeLibrarianBarcodeScannerModal();
                }
            });

            const setActive = (hash) => {
                const sectionId = sectionMap[hash] || "overview";

                sections.forEach((section) => {
                    section.classList.toggle("active", section.dataset.section === sectionId);
                });

                document.querySelectorAll("[data-view]").forEach((item) => {
                    item.classList.toggle("visible", item.dataset.view === hash);
                });

                navLinks.forEach((link) => {
                    link.classList.toggle("active", link.dataset.navLink === hash);
                });

                groups.forEach((group) => {
                    const shouldOpen = group.dataset.group === groupMap[hash];
                    group.classList.toggle("open", shouldOpen);

                    const toggle = group.querySelector("[data-group-toggle]");
                    if (toggle) {
                        toggle.setAttribute("aria-expanded", shouldOpen ? "true" : "false");
                    }
                });
            };

            const fromHash = () => {
                const id = window.location.hash.replace("#", "");
                const active = sectionMap[id] ? id : "overview";
                setActive(active);
            };

            toggles.forEach((toggle) => {
                toggle.addEventListener("click", () => {
                    const group = toggle.closest("[data-group]");
                    if (!group) {
                        return;
                    }

                    const willOpen = !group.classList.contains("open");
                    group.classList.toggle("open", willOpen);
                    toggle.setAttribute("aria-expanded", willOpen ? "true" : "false");
                });
            });

            navLinks.forEach((link) => {
                link.addEventListener("click", () => {
                    const target = link.dataset.navLink;
                    if (sectionMap[target]) {
                        setActive(target);
                    }
                });
            });

            document.querySelectorAll(".js-edit-announcement").forEach((button) => {
                button.addEventListener("click", () => {
                    const payload = button.dataset.announcement;
                    if (!payload) {
                        return;
                    }

                    openAnnouncementModal(JSON.parse(payload));
                });
            });

            document.querySelectorAll(".js-edit-book").forEach((button) => {
                button.addEventListener("click", () => {
                    const payload = button.dataset.book;
                    if (!payload) {
                        return;
                    }

                    openEditBookModal(JSON.parse(payload));
                });
            });

            if (editBookModal) {
                editBookModal.addEventListener("click", (event) => {
                    if (event.target === editBookModal) {
                        window.closeEditBookModal();
                    }
                });
            }

            if (announcementModal) {
                announcementModal.addEventListener("click", (event) => {
                    if (event.target === announcementModal) {
                        window.closeAnnouncementModal();
                    }
                });
            }

            window.addEventListener("hashchange", fromHash);
            window.addEventListener("load", fromHash);
            fromHash();
        })();
    </script>
<script>
    const librarianCreatePasswordInput = document.getElementById('user-create-password');
    const librarianCreateStrengthFill = document.getElementById('user-create-strength-fill');
    const librarianCreateStrengthText = document.getElementById('user-create-strength-text');

    if (librarianCreatePasswordInput && librarianCreateStrengthFill && librarianCreateStrengthText) {
        const scorePassword = (value) => {
            let score = 0;
            if (!value) return 0;
            if (value.length >= 8) score += 1;
            if (/[A-Z]/.test(value)) score += 1;
            if (/[a-z]/.test(value)) score += 1;
            if (/\d/.test(value)) score += 1;
            if (/[^A-Za-z0-9]/.test(value)) score += 1;
            return score;
        };

        const renderLibrarianCreateStrength = () => {
            const score = scorePassword(librarianCreatePasswordInput.value);
            const percent = Math.min(100, score * 20);
            const colors = ['#ef4444', '#f59e0b', '#eab308', '#22c55e', '#16a34a'];
            const labels = ['Very weak', 'Weak', 'Fair', 'Good', 'Strong'];
            const index = Math.min(labels.length - 1, Math.max(0, score - 1));
            librarianCreateStrengthFill.style.width = `${percent}%`;
            librarianCreateStrengthFill.style.background = colors[index];
            librarianCreateStrengthText.textContent = librarianCreatePasswordInput.value ? labels[index] : 'Enter a password';
        };

        librarianCreatePasswordInput.addEventListener('input', renderLibrarianCreateStrength);
        renderLibrarianCreateStrength();
    }
</script>
<script>
    const chatPanel = document.getElementById('chatPanel');
    const chatLauncher = document.getElementById('chatLauncher');
    const chatClose = document.getElementById('chatClose');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatLog = document.getElementById('chatLog');
    const chatSend = document.getElementById('chatSend');
    const chatNew = document.getElementById('chatNew');
    const chatStateKey = 'librarya_librarian_chat_state';

    const saveChatState = () => {
        if (!chatPanel || !chatLog) return;
        const messages = Array.from(chatLog.querySelectorAll('.chat-bubble')).map((bubble) => ({
            role: bubble.classList.contains('user') ? 'user' : 'bot',
            text: bubble.textContent ?? '',
        }));
        localStorage.setItem(chatStateKey, JSON.stringify({
            open: chatPanel.classList.contains('open'),
            messages,
        }));
    };

    const restoreChatState = () => {
        try {
            const raw = localStorage.getItem(chatStateKey);
            if (!raw) return;
            const state = JSON.parse(raw);
            if (Array.isArray(state.messages) && state.messages.length > 0) {
                chatLog.innerHTML = '';
                state.messages.forEach((item) => appendChat(item.text, item.role === 'user' ? 'user' : 'bot'));
            }
            if (state.open) {
                toggleChat(true);
            }
        } catch (error) {
            localStorage.removeItem(chatStateKey);
        }
    };
    const startNewChat = () => {
        chatLog.innerHTML = '';
        appendChat('Hi, I am your library assistant. Ask me anything about the system.', 'bot');
        chatPanel.classList.add('open');
        chatPanel.setAttribute('aria-hidden', 'false');
        localStorage.removeItem(chatStateKey);
        saveChatState();
    };
    const appendChat = (text, role) => {
        const bubble = document.createElement('div');
        bubble.className = `chat-bubble ${role}`;
        bubble.textContent = text;
        chatLog.appendChild(bubble);
        chatLog.scrollTop = chatLog.scrollHeight;
    };
    const toggleChat = (open) => {
        chatPanel.classList.toggle('open', open);
        chatPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (open) chatInput.focus();
        saveChatState();
    };
    chatLauncher?.addEventListener('click', () => toggleChat(!chatPanel.classList.contains('open')));
    chatClose?.addEventListener('click', () => toggleChat(false));
    chatNew?.addEventListener('click', startNewChat);
    chatForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const message = chatInput.value.trim();
        if (!message) return;
        appendChat(message, 'user');
        chatInput.value = '';
        chatSend.disabled = true;
        appendChat('Thinking...', 'bot');
        const thinkingBubble = chatLog.lastElementChild;
        try {
            const response = await fetch("{{ route('chat.send') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ message }),
            });
            const data = await response.json();
            thinkingBubble.textContent = data.reply ?? 'No reply received.';
            saveChatState();
        } catch (error) {
            thinkingBubble.textContent = 'Sorry, I could not reach the chat service.';
            saveChatState();
        } finally {
            chatSend.disabled = false;
            chatLog.scrollTop = chatLog.scrollHeight;
        }
    });

    restoreChatState();
</script>
<script>
    (function(){
        // Prepare data from blade variables
        const dashboardDataElement = document.getElementById('librarian-dashboard-data');
        const dashboardData = dashboardDataElement ? JSON.parse(dashboardDataElement.textContent || '{}') : { topBooks: [], genres: [], trend: [] };
        const topBooks = dashboardData.topBooks || [];
        const genres = dashboardData.genres || [];
        const trend = dashboardData.trend || [];

        // Top Books Bar Chart
        try {
            const ctxTop = document.getElementById('chartTopBooks');
            if (ctxTop) {
                const labels = topBooks.map(b => b.title);
                const data = topBooks.map(b => b.count);
                new Chart(ctxTop, {
                    type: 'bar',
                    data: { labels, datasets: [{ label: 'Borrows', data, backgroundColor: '#0b6b3a' }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }
        } catch (e) { console.warn('Top books chart failed', e); }

        // Genres Doughnut Chart
        try {
            const ctxG = document.getElementById('chartGenres');
            if (ctxG) {
                const labels = genres.map(g => g.genre);
                const data = genres.map(g => g.count);
                const colors = labels.map((_, i) => `hsl(${(i*47)%360} 60% 45%)`);
                new Chart(ctxG, {
                    type: 'doughnut',
                    data: { labels, datasets: [{ data, backgroundColor: colors }] },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
        } catch (e) { console.warn('Genres chart failed', e); }

        // 6-Month Trend Line Chart
        try {
            const ctxT = document.getElementById('chartTrend');
            if (ctxT) {
                const labels = trend.map(t => t.month);
                const data = trend.map(t => t.count);
                new Chart(ctxT, {
                    type: 'line',
                    data: { labels, datasets: [{ label: 'Borrows', data, borderColor: '#0b6b3a', backgroundColor: 'rgba(11,107,58,0.08)', fill: true, tension: 0.35 }] },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
        } catch (e) { console.warn('Trend chart failed', e); }
    })();
</script>
</body>
</html>
