<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard | ISU Library System</title>
    <link rel="stylesheet" href="{{ asset('css/isu-ui-fix.css') }}">
    <style>
        :root {
            --green: #0b6b3a;
            --green-deep: #064225;
            --gold: #f2c84b;
            --red: #c8282d;
            --bg: #f4f8f1;
            --panel: rgba(255, 255, 255, 0.94);
            --text: #173423;
            --muted: #617265;
            --line: rgba(11, 107, 58, 0.14);
            --shadow: 0 1.1rem 2.8rem rgba(6, 66, 37, 0.10);
            --sidebar-width: clamp(17rem, 22vw, 18.5rem);
        }

        * { box-sizing: border-box; }

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
                radial-gradient(circle at top left, rgba(242, 200, 75, 0.16), transparent 24rem),
                radial-gradient(circle at top right, rgba(11, 107, 58, 0.12), transparent 18rem),
                linear-gradient(180deg, #f8fbf5 0%, var(--bg) 100%);
            overflow-x: hidden;
        }

        a { color: inherit; }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: clamp(0.75rem, 2vw, 1.25rem);
            padding: clamp(0.9rem, 2vw, 1.25rem) clamp(1rem, 3vw, 2rem);
            background: rgba(6, 66, 37, 0.94);
            color: #fff;
            backdrop-filter: blur(16px);
            box-shadow: 0 10px 30px rgba(6, 66, 37, 0.16);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: clamp(0.8rem, 1.4vw, 0.95rem);
            text-decoration: none;
        }

        .brand img {
            width: clamp(3rem, 5vw, 3.85rem);
            height: clamp(3rem, 5vw, 3.85rem);
            object-fit: cover;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 12px 26px rgba(0, 0, 0, 0.15);
        }

        .brand strong,
        .brand span { display: block; }

        .brand span {
            margin-top: 0.1rem;
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
            font-size: clamp(0.82rem, 0.35vw + 0.74rem, 0.96rem);
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
            gap: clamp(0.85rem, 1.6vw, 1.1rem);
            border: 1px solid var(--line);
            border-radius: clamp(1rem, 1.4vw, 1.35rem);
            padding: clamp(0.9rem, 1.8vw, 1.15rem);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(245, 250, 242, 0.94));
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
        .sidebar-header p { margin: 0; }

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
            letter-spacing: clamp(0.08em, 0.2vw, 0.12em);
            text-transform: uppercase;
        }

        .nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border-radius: 0.95rem;
            padding: clamp(0.75rem, 1.2vw, 0.95rem) clamp(0.85rem, 1.2vw, 1rem);
            background: transparent;
            color: var(--green-deep);
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .nav-link:hover,
        .nav-link:focus-visible,
        .nav-link.active {
            color: #fff;
            background: linear-gradient(135deg, var(--green), #0a7a43);
            transform: translateX(2px);
            outline: none;
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
            gap: clamp(1rem, 2vw, 1.4rem);
            width: 100%;
            max-width: min(100%, 92rem);
            margin-inline: auto;
        }

        .hero {
            display: grid;
            gap: clamp(0.9rem, 1.5vw, 1.2rem);
            padding: clamp(1.1rem, 3vw, 1.8rem);
            border: 1px solid var(--line);
            border-radius: clamp(1.1rem, 1.8vw, 1.5rem);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(245, 250, 242, 0.8));
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
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
            letter-spacing: clamp(0.06em, 0.15vw, 0.08em);
            text-transform: uppercase;
        }

        .hero h1 {
            margin: clamp(0.55rem, 1vw, 0.7rem) 0 0;
            color: var(--green-deep);
            font-size: clamp(1.9rem, 4.2vw, 3.5rem);
            line-height: clamp(1.02, 0.95 + 0.1vw, 1.12);
        }

        .hero p {
            max-width: min(100%, 52rem);
            margin: clamp(0.7rem, 1.2vw, 0.95rem) 0 0;
            color: var(--muted);
            line-height: clamp(1.5, 1.2 + 0.2vw, 1.85);
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: clamp(0.55rem, 1vw, 0.85rem);
            margin-top: clamp(0.8rem, 1.5vw, 1.1rem);
        }

        .btn,
        .action-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: clamp(2.75rem, 4vw, 3.15rem);
            padding: 0.72rem clamp(0.95rem, 1.4vw, 1.15rem);
            border-radius: 0.95rem;
            border: 1px solid transparent;
            font: inherit;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .btn:hover,
        .btn:focus-visible,
        .action-link:hover,
        .action-link:focus-visible {
            transform: translateY(-1px);
            outline: none;
        }

        .btn-green,
        .action-link.primary {
            background: linear-gradient(135deg, var(--green), #0a7a43);
            color: #fff;
            box-shadow: 0 12px 26px rgba(11, 107, 58, 0.22);
        }

        .btn-blue {
            background: linear-gradient(135deg, #275e8a, #1e4670);
            color: #fff;
        }

        .btn-red {
            background: linear-gradient(135deg, var(--red), #9e2226);
            color: #fff;
        }

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
        .stack-card {
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
            letter-spacing: clamp(0.06em, 0.12vw, 0.08em);
            text-transform: uppercase;
        }

        .status-card .count {
            color: var(--green-deep);
            font-size: clamp(1.9rem, 5vw, 3.4rem);
            font-weight: 900;
            line-height: clamp(0.92, 0.88 + 0.06vw, 1);
        }

        .status-card p {
            margin: 0;
            color: var(--muted);
            line-height: clamp(1.45, 1.1 + 0.15vw, 1.65);
        }

        .status-card.green { border-top: 0.45rem solid var(--green); }
        .status-card.gold  { border-top: 0.45rem solid var(--gold); }
        .status-card.orange { border-top: 0.45rem solid #e67e22; }
        .status-card.red   { border-top: 0.45rem solid var(--red); }

        .panel { overflow: hidden; }

        .panel-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            padding: clamp(1rem, 2vw, 1.2rem) clamp(1rem, 2vw, 1.25rem) clamp(0.85rem, 1.5vw, 1rem);
            border-bottom: 1px solid var(--line);
            flex-wrap: wrap;
        }

        .panel-header h2 {
            margin: 0;
            color: var(--green-deep);
            font-size: clamp(1.2rem, 2vw, 1.75rem);
            line-height: clamp(1.08, 1.04 + 0.08vw, 1.2);
        }

        .panel-header p {
            margin: 0.35rem 0 0;
            color: var(--muted);
        }

        .panel-subtle { padding: 0 clamp(1rem, 2vw, 1.25rem) clamp(1rem, 2vw, 1.25rem); }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: clamp(0.6rem, 1vw, 0.85rem);
            align-items: center;
            padding: clamp(0.9rem, 2vw, 1.1rem) clamp(1rem, 2vw, 1.25rem) clamp(1rem, 2vw, 1.25rem);
        }

        .toolbar input[type="search"],
        .toolbar select {
            flex: 1 1 200px;
            min-width: 0;
        }

        .stack-form { padding: clamp(0.9rem, 2vw, 1.1rem) clamp(1rem, 2vw, 1.25rem) clamp(1rem, 2vw, 1.25rem); }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.94);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 100%;
        }

        th, td {
            padding: clamp(0.75rem, 1.2vw, 0.95rem) clamp(0.85rem, 1.4vw, 1rem);
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }

        th {
            background: rgba(242, 200, 75, 0.14);
            color: var(--green-deep);
            font-size: clamp(0.72rem, 0.35vw + 0.64rem, 0.85rem);
            letter-spacing: clamp(0.06em, 0.12vw, 0.08em);
            text-transform: uppercase;
        }

        tr:last-child td { border-bottom: 0; }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.3rem 0.55rem;
            background: rgba(242, 200, 75, 0.22);
            color: var(--green-deep);
            font-size: clamp(0.72rem, 0.3vw + 0.66rem, 0.84rem);
            font-weight: 800;
            white-space: nowrap;
        }

        .badge.good   { background: rgba(11, 107, 58, 0.12); color: var(--green); }
        .badge.warn   { background: rgba(230, 126, 34, 0.16); color: #b45c11; }
        .badge.danger { background: rgba(200, 40, 45, 0.13); color: var(--red); }

        .flash, .error-box {
            margin: 0 clamp(1rem, 2vw, 1.25rem) clamp(1rem, 2vw, 1.25rem);
            border: 1px solid var(--line);
            border-radius: 1rem;
            padding: clamp(0.9rem, 1.6vw, 1rem) clamp(0.95rem, 1.6vw, 1.05rem);
        }

        .flash {
            color: var(--green-deep);
            background: rgba(242, 200, 75, 0.18);
            font-weight: 700;
        }

        .error-box {
            color: var(--red);
            background: rgba(200, 40, 45, 0.08);
        }

        .cards-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(clamp(16rem, 36vw, 24rem), 1fr));
            gap: clamp(0.85rem, 1.8vw, 1.1rem);
            padding: 0 clamp(1rem, 2vw, 1.25rem) clamp(1rem, 2vw, 1.25rem);
        }

        .cards-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(clamp(15rem, 28vw, 20rem), 1fr));
            gap: clamp(0.85rem, 1.8vw, 1.1rem);
            padding: 0 clamp(1rem, 2vw, 1.25rem) clamp(1rem, 2vw, 1.25rem);
        }

        .stack-card { padding: clamp(0.95rem, 1.8vw, 1.1rem); }

        .stack-card h3 {
            margin: 0 0 clamp(0.45rem, 0.9vw, 0.6rem);
            color: var(--green-deep);
        }

        .stack-card p, .stack-card ul {
            margin: 0;
            color: var(--muted);
            line-height: clamp(1.5, 1.15 + 0.2vw, 1.75);
        }

        .stack-card ul { padding-left: 1.05rem; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
            margin-top: 0.8rem;
        }

        .field { display: grid; gap: 0.4rem; }
        .field.full { grid-column: 1 / -1; }

        .field label {
            color: var(--green-deep);
            font-size: clamp(0.82rem, 0.35vw + 0.74rem, 0.95rem);
            font-weight: 700;
        }

        input, select, textarea {
            width: 100%;
            min-height: clamp(2.75rem, 4vw, 3rem);
            border: 1px solid var(--line);
            border-radius: 0.9rem;
            padding: 0.72rem clamp(0.8rem, 1.2vw, 0.95rem);
            background: rgba(255, 255, 255, 0.96);
            color: var(--text);
            font: inherit;
        }

        textarea { min-height: 7rem; resize: vertical; }

        input:focus, select:focus, textarea:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 4px rgba(11, 107, 58, 0.12);
            outline: none;
        }

        .muted { color: var(--muted); }

        .actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }

        .modal {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: none;
            align-items: center;
            justify-content: center;
            padding: clamp(0.85rem, 2vw, 1rem);
            background: rgba(6, 20, 10, 0.55);
            backdrop-filter: blur(6px);
        }

        .modal.open { display: flex; }

        .modal-box {
            width: min(62rem, 100%);
            max-height: calc(100dvh - 2rem);
            overflow: auto;
            border-radius: 1.25rem;
            border: 1px solid rgba(255,255,255,0.22);
            background: #fff;
            box-shadow: 0 28px 80px rgba(0,0,0,0.25);
        }

        .modal-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: clamp(1rem, 2vw, 1.15rem) clamp(1rem, 2vw, 1.25rem);
            border-bottom: 1px solid var(--line);
        }

        .modal-head h3 { margin: 0; color: var(--green-deep); }

        .modal-close {
            border: 0;
            border-radius: 999px;
            width: clamp(2.25rem, 4vw, 2.6rem);
            height: clamp(2.25rem, 4vw, 2.6rem);
            background: rgba(11, 107, 58, 0.1);
            color: var(--green-deep);
            font: inherit;
            font-size: clamp(1.1rem, 1vw + 0.75rem, 1.35rem);
            font-weight: 900;
            cursor: pointer;
        }

        .modal-body { padding: clamp(1rem, 2vw, 1.25rem); }

        .details-cell {
            font-size: clamp(0.78rem, 0.35vw + 0.7rem, 0.9rem);
            color: var(--muted);
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
        }

        .details-cell .kv {
            display: inline-flex;
            gap: 0.25rem;
            background: rgba(11,107,58,0.07);
            border-radius: 999px;
            padding: clamp(0.18rem, 0.35vw, 0.24rem) clamp(0.45rem, 0.8vw, 0.55rem);
            font-size: clamp(0.72rem, 0.3vw + 0.66rem, 0.84rem);
        }

        .details-cell .kv strong { color: var(--green-deep); }

        .section-anchor { scroll-margin-top: 7rem; }

        .chart-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(clamp(16rem, 34vw, 24rem), 1fr)); gap: clamp(0.85rem, 1.8vw, 1.1rem); padding: 0 clamp(1rem, 2vw, 1.25rem) clamp(1rem, 2vw, 1.25rem); }
        .chart-box { border: 1px solid var(--line); border-radius: 1rem; background: rgba(255,255,255,0.96); padding: clamp(1rem, 2vw, 1.25rem); }
        .chart-box h3 { margin: 0 0 clamp(0.85rem, 1.6vw, 1rem); color: var(--green-deep); font-size: clamp(0.95rem, 0.8vw + 0.75rem, 1.15rem); }
        .bar-chart { display: flex; flex-direction: column; gap: 0.6rem; }
        .bar-row { display: grid; grid-template-columns: minmax(7rem, 9rem) 1fr minmax(2.5rem, 3rem); align-items: center; gap: 0.6rem; font-size: clamp(0.78rem, 0.35vw + 0.7rem, 0.9rem); }
        .bar-track { height: 0.7rem; border-radius: 999px; background: var(--line); overflow: hidden; }
        .bar-fill { width: 0; height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--green), #0a7a43); }
        .bar-fill.gold   { background: linear-gradient(90deg, #e5b927, var(--gold)); }
        .bar-fill.red    { background: linear-gradient(90deg, var(--red), #9e2226); }
        .bar-fill.blue   { background: linear-gradient(90deg, #275e8a, #1e4670); }
        .bar-fill.orange { background: linear-gradient(90deg, #c0620a, #e67e22); }
        .bar-val { color: var(--muted); font-size: clamp(0.74rem, 0.3vw + 0.66rem, 0.85rem); text-align: right; }
        .chart-label {
            font-size: clamp(0.72rem, 0.3vw + 0.64rem, 0.84rem);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .stars { display: inline-flex; align-items: center; gap: 0.1rem; }
        .star { color: var(--gold); font-size: clamp(0.95rem, 0.6vw + 0.8rem, 1.1rem); line-height: 1; }
        .rating-value { color: var(--text); font-weight: 600; font-size: 0.85rem; margin-left: 0.35rem; }
        .star.empty { color: var(--line); }
        .fine-amount { font-weight: 800; color: var(--red); }
        .notif-bell { position: relative; display: inline-flex; align-items: center; justify-content: center; width: clamp(2.2rem, 4vw, 2.5rem); height: clamp(2.2rem, 4vw, 2.5rem); border-radius: 50%; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.22); color: #fff; cursor: pointer; font-size: clamp(1rem, 0.8vw + 0.78rem, 1.2rem); text-decoration: none; }
        .notif-dot { position: absolute; top: 0.2rem; right: 0.2rem; width: clamp(0.45rem, 0.8vw, 0.6rem); height: clamp(0.45rem, 0.8vw, 0.6rem); border-radius: 50%; background: var(--red); }
        .export-bar { display: flex; flex-wrap: wrap; gap: 0.6rem; padding: clamp(0.7rem, 1.4vw, 0.9rem) clamp(1rem, 2vw, 1.25rem); border-bottom: 1px solid var(--line); background: rgba(242,200,75,0.06); }
        .export-bar span { font-size: clamp(0.78rem, 0.35vw + 0.7rem, 0.9rem); color: var(--muted); align-self: center; }
        .notif-list { display: grid; gap: 0.5rem; }
        .notif-item { display: flex; gap: 0.8rem; padding: clamp(0.75rem, 1.5vw, 0.95rem); border-radius: 0.85rem; border: 1px solid var(--line); background: rgba(255,255,255,0.9); }
        .notif-item.unread { background: rgba(11,107,58,0.05); border-color: rgba(11,107,58,0.18); }
        .notif-icon { font-size: clamp(1.05rem, 0.8vw + 0.8rem, 1.25rem); flex-shrink: 0; line-height: 1.4; }
        .notif-body strong { display: block; color: var(--green-deep); font-size: clamp(0.82rem, 0.35vw + 0.74rem, 0.95rem); }
        .notif-body span   { color: var(--muted); font-size: clamp(0.74rem, 0.3vw + 0.66rem, 0.85rem); }

        @media (max-width: 1024px) {
            .layout { grid-template-columns: 1fr; }
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
            .status-grid { grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr)); }
        }

        @media (max-width: 600px) {
            .topbar { align-items: flex-start; flex-direction: column; }
            .topbar-meta { justify-content: flex-start; }
            .cards-2, .cards-3, .form-grid, .status-grid { grid-template-columns: 1fr; }
            .panel-header { align-items: flex-start; }
            table { min-width: 100%; }
            .table-wrap { overflow-x: auto; }
        }

    </style>
</head>
<body>
    @php
        $page      = $dashboardPage ?? 'dashboard';
        $isActive  = fn (string $name) => $page === $name ? 'active' : '';
        $user      = auth()->user();
        $queryParams = request()->query();

        $stats = $stats ?? [
            'books' => 0, 'available_books' => 0,
            'reservations' => 0, 'loans' => 0, 'overdue' => 0,
            'users' => ['admin'=>0,'librarian'=>0,'instructor'=>0,'student'=>0,'guest'=>0],
        ];

        $users        = collect($users);
        $books        = collect($books);
        $reservations = collect($reservations);
        $loans        = collect($loans);
        $historyItems = collect($historyItems);
        $announcements = collect($announcements);
        $activityLogs  = collect($activityLogs);
        $loginLogs     = collect($loginLogs);
        $genres        = collect($genres);
        $topBorrowedBooks   = collect($topBorrowedBooks);
        $borrowedGenreStats = collect($borrowedGenreStats);
        $borrowTrend        = collect($borrowTrend);
        $chartMonths        = collect($chartMonths);

        $roleOptions = [
            'admin'      => 'Admin',
            'librarian'  => 'Librarian',
            'instructor' => 'Instructor',
            'student'    => 'Student',
            'guest'      => 'Guest',
        ];

        $createRoleOptions = [
            'librarian'  => 'Librarian',
            'instructor' => 'Instructor',
            'student'    => 'Student',
        ];

        $audienceOptions = [
            'public'     => 'Public',
            'all'        => 'All',
            'admin'      => 'Admin',
            'librarian'  => 'Librarian',
            'instructor' => 'Instructor',
            'student'    => 'Student',
            'guest'      => 'Guest',
        ];

        $bookStatuses         = ['Available', 'Unavailable', 'Archived'];
        $announcementStatuses = ['Draft', 'Published', 'Archived'];

        $safeBookFields = ['isbn','title','author','genre','year','quantity','available_quantity','location','status','description'];
        $safeAnnouncementFields = ['id','title','body','audience','status','published_at'];
    @endphp

    <header class="topbar">
        <a class="brand" href="{{ route('home') }}">
            <img src="{{ asset('picture/ISU.jpg') }}" alt="ISU logo">
            <span>
                <strong>ISU Library System</strong>
                <span>Admin dashboard</span>
            </span>
        </a>
        <div class="topbar-meta">
            <span class="pill">Role: Admin</span>
            <span class="pill">Signed in as {{ $user->name ?? 'Admin' }}</span>
            @php $unread = $unreadCount ?? 0; @endphp
            <a class="notif-bell" href="{{ route('admin.notifications') }}" title="Notifications">
                🔔@if($unread > 0)<span class="notif-dot"></span>@endif
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout" type="submit">Log out</button>
            </form>
        </div>
    </header>

    <div class="layout">
        <aside class="sidebar" aria-label="Admin navigation">
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <img src="{{ asset('picture/ISU.jpg') }}" alt="ISU logo">
                    <h2>Admin Panel</h2>
                    <p>Manage users, books, announcements, reports, and activity from one workspace.</p>
                </div>

                <nav class="nav-group">
                    <p class="nav-group-label">Workspace</p>
                    <a class="nav-link {{ $isActive('dashboard') }}"    href="{{ route('dashboard.admin') }}">Dashboard</a>
                    <a class="nav-link {{ $isActive('users') }}"        href="{{ route('admin.users', $queryParams) }}">Users</a>
                    <a class="nav-link {{ $isActive('books') }}"        href="{{ route('admin.books', $queryParams) }}">Books</a>
                    <a class="nav-link {{ $isActive('reports') }}"      href="{{ route('admin.reports', $queryParams) }}">Reports</a>
                    <a class="nav-link {{ $isActive('charts') }}"       href="{{ route('admin.charts', $queryParams) }}">Charts</a>
                    <a class="nav-link {{ $isActive('activity') }}"     href="{{ route('admin.activity', $queryParams) }}">Activity</a>
                    <a class="nav-link {{ $isActive('login-history') }}" href="{{ route('admin.login-history', $queryParams) }}">Login History</a>
                    <a class="nav-link {{ $isActive('announcements') }}" href="{{ route('admin.announcements', $queryParams) }}">Announcements</a>
                    <a class="nav-link {{ $isActive('fines') }}"         href="{{ route('admin.fines', $queryParams) }}">Fines</a>
                    <a class="nav-link {{ $isActive('reviews') }}"       href="{{ route('admin.reviews', $queryParams) }}">Reviews</a>
                    <a class="nav-link {{ $isActive('notifications') }}" href="{{ route('admin.notifications', $queryParams) }}">Notifications</a>
                </nav>
            </div>
        </aside>

        <main>
            <div class="shell">

                @if (session('status'))
                    <div class="flash">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="error-box">
                        <strong>Please review the form errors below.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($page === 'dashboard')
                    <section class="hero">
                        <span class="eyebrow">Admin control center</span>
                        <h1>Welcome, {{ $user->name ?? 'Admin' }}.</h1>
                        <p>{{ $message ?? 'Manage accounts, permissions, library records, and system-level activity from one responsive dashboard.' }}</p>
                        <div class="hero-actions">
                            <a class="action-link primary"   href="{{ route('admin.books') }}">Manage Books</a>
                            <a class="action-link secondary" href="{{ route('admin.users') }}">Manage Users</a>
                            <a class="action-link secondary" href="{{ route('admin.announcements') }}">Announcements</a>
                        </div>
                    </section>

                    <section class="status-grid" aria-label="Admin statistics">
                        <div class="status-card green">
                            <span class="label">Books</span>
                            <span class="count">{{ $stats['books'] }}</span>
                            <p>Complete collection in the catalog.</p>
                        </div>
                        <div class="status-card gold">
                            <span class="label">Available Books</span>
                            <span class="count">{{ $stats['available_books'] }}</span>
                            <p>Items ready for borrowing or reservation.</p>
                        </div>
                        <div class="status-card orange">
                            <span class="label">Reservations</span>
                            <span class="count">{{ $stats['reservations'] }}</span>
                            <p>Reserved books waiting to be processed.</p>
                        </div>
                        <div class="status-card red">
                            <span class="label">Overdue</span>
                            <span class="count">{{ $stats['overdue'] }}</span>
                            <p>Loans that need follow up.</p>
                        </div>
                    </section>

                   

                    <section class="panel">
                        <div class="panel-header">
                            <div>
                                <h2>Recent Logins</h2>
                                <p>The latest login events captured by the system.</p>
                            </div>
                        </div>
                        <div class="panel-subtle">
                            <div class="table-wrap">
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
                        </div>
                    </section>

                @elseif ($page === 'users')
                    <section class="panel section-anchor" id="users">
                        <div class="panel-header">
                            <div>
                                <h2>User Management</h2>
                                <p>Create accounts, search users, and manage account status from one place.</p>
                            </div>
                            <div class="hero-actions" style="margin-top:0;">
                                <button class="btn btn-green" type="button" onclick="openModal('userCreateModal')">Add User</button>
                            </div>
                        </div>

                        <form class="toolbar" method="GET" action="{{ route('admin.users') }}">
                            <input name="search" type="search" value="{{ $search }}" placeholder="Search by name, email, or login ID">
                            <select name="role" aria-label="Filter by role">
                                <option value="">All roles</option>
                                @foreach ($roleOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($roleFilter === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-green" type="submit">Filter</button>
                            <a class="action-link secondary" href="{{ route('admin.users') }}">Reset</a>
                        </form>

                        <div class="panel-subtle">
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Login ID</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($users as $userRow)
                                            <tr>
                                                <td><strong>{{ $userRow->name }}</strong></td>
                                                <td>{{ $userRow->email }}</td>
                                                <td>{{ $userRow->login_id ?? 'N/A' }}</td>
                                                <td>{{ ucfirst($userRow->role) }}</td>
                                                <td>
                                                    <span class="badge {{ ($userRow->status ?? 'active') === 'active' ? 'good' : 'danger' }}">
                                                        {{ ucfirst($userRow->status ?? 'active') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($userRow->id !== $user?->id)
                                                        <div class="actions">
                                                            <form method="POST" action="{{ route('admin.users.status', $userRow->id) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button class="btn {{ ($userRow->status ?? 'active') === 'active' ? 'btn-red' : 'btn-green' }}" type="submit">
                                                                    {{ ($userRow->status ?? 'active') === 'active' ? 'Deactivate' : 'Activate' }}
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <span class="muted">Current account</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="muted">No users found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                @elseif ($page === 'books')
                    <section class="panel section-anchor" id="books">
                        <div class="panel-header">
                            <div>
                                <h2>Book Management</h2>
                                <p>Add and edit catalog entries from one form-driven workspace.</p>
                            </div>
                            <div class="hero-actions" style="margin-top:0;">
                                <button class="btn btn-green" type="button" onclick="openModal('bookCreateModal')">Add Book</button>
                            </div>
                        </div>

                        <form class="toolbar" method="GET" action="{{ route('admin.books') }}">
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
                            <a class="action-link secondary" href="{{ route('admin.books') }}">Reset</a>
                        </form>

                        <datalist id="genreOptions">
                            @foreach ($genres as $genre)
                                <option value="{{ $genre }}">
                            @endforeach
                        </datalist>

                        <div class="stack-form">
                            <div class="stack-card">
                                <h3>Issue Book</h3>
                                <p>Issue a catalog item directly to a borrower by ISBN and user ID.</p>
                                <form method="POST" action="{{ route('admin.books.issue', 'ISBN-HERE') }}" onsubmit="this.action = this.action.replace('ISBN-HERE', encodeURIComponent(this.querySelector('[name=isbn]').value));">
                                    @csrf
                                    <div class="form-grid">
                                        <div class="field">
                                            <label>ISBN</label>
                                            <input name="isbn" type="text" placeholder="978-0132350884" required>
                                        </div>
                                        <div class="field">
                                            <label>Borrower User ID</label>
                                            <input name="user_id" type="number" min="1" placeholder="Borrower ID" required>
                                        </div>
                                    </div>
                                    <div class="hero-actions">
                                        <button class="btn btn-green" type="submit">Issue Book</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="panel-subtle">
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
                                        @forelse ($books as $book)
                                            @php
                                                $bookJs = json_encode(array_intersect_key($book, array_flip($safeBookFields)));
                                            @endphp
                                            <tr>
                                                <td>{{ $book['isbn'] }}</td>
                                                <td><strong>{{ $book['title'] }}</strong></td>
                                                <td>{{ $book['author'] }}</td>
                                                <td>{{ $book['genre'] }}</td>
                                                <td>{{ $book['year'] ?? 'N/A' }}</td>
                                                <td>{{ $book['quantity'] }}</td>
                                                <td>{{ $book['available_quantity'] }}</td>
                                                <td>{{ $book['location'] ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge {{ $book['status'] === 'Available' ? 'good' : ($book['status'] === 'Unavailable' ? 'danger' : 'warn') }}">
                                                        {{ $book['status'] }}
                                                    </span>
                                                </td>
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
                                                    <div class="actions">
                                                        <button class="btn btn-blue js-edit-book" type="button" data-book="{{ $bookJs }}">Edit</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="10" class="muted">No books found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                @elseif ($page === 'reports')
                    <section class="hero">
                        <span class="eyebrow">Reports</span>
                        <h1>Library Operations</h1>
                        <p>Track circulation, active loans, and the broader state of the library at a glance.</p>
                    </section>

                    <section class="status-grid" aria-label="Report summary">
                        <div class="status-card green">
                            <span class="label">Users</span>
                            <span class="count">{{ array_sum(is_array($stats['users'] ?? null) ? $stats['users'] : []) }}</span>
                            <p>Total user accounts in the system.</p>
                        </div>
                        <div class="status-card gold">
                            <span class="label">Active Loans</span>
                            <span class="count">{{ $stats['loans'] }}</span>
                            <p>Items currently borrowed.</p>
                        </div>
                        <div class="status-card orange">
                            <span class="label">Reservations</span>
                            <span class="count">{{ $stats['reservations'] }}</span>
                            <p>Pending reservation queue.</p>
                        </div>
                        <div class="status-card red">
                            <span class="label">Overdue</span>
                            <span class="count">{{ $stats['overdue'] }}</span>
                            <p>Loans past due date.</p>
                        </div>
                    </section>

                    <section class="panel">
                        <div class="panel-header">
                            <div><h2>Reservations</h2><p>Current reservations awaiting processing.</p></div>
                        </div>
                        <div class="export-bar">
                            <span>Export:</span>
                            <a class="btn action-link secondary" href="{{ route('admin.reports.export', ['type'=>'reservations','format'=>'csv']) }}">CSV</a>
                            <a class="btn action-link secondary" href="{{ route('admin.reports.export', ['type'=>'reservations','format'=>'pdf']) }}">PDF</a>
                        </div>
                        <div class="panel-subtle">
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr><th>Borrower</th><th>Book</th><th>Borrower ID</th><th>Reserved At</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($reservations as $reservation)
                                            <tr>
                                                <td><strong>{{ $reservation->borrower_name }}</strong></td>
                                                <td>{{ $reservation->title }}<div class="muted">{{ $reservation->isbn }}</div></td>
                                                <td>{{ $reservation->borrower_id }}</td>
                                                <td>{{ $reservation->reserved_at }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="muted">No active reservations.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="panel">
                        <div class="panel-header">
                            <div><h2>Active Loans</h2><p>Books currently borrowed and not yet returned.</p></div>
                        </div>
                        <div class="panel-subtle">
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr><th>Borrower</th><th>Book</th><th>Borrowed</th><th>Due</th><th>Status</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($loans as $loan)
                                            <tr>
                                                <td><strong>{{ $loan->borrower_name }}</strong></td>
                                                <td>{{ $loan->title }}<div class="muted">{{ $loan->isbn }}</div></td>
                                                <td>{{ $loan->borrowed_at }}</td>
                                                <td>{{ $loan->due_at }}</td>
                                                <td><span class="badge {{ $loan->status === 'Borrowed' ? 'warn' : 'good' }}">{{ $loan->status }}</span></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="muted">No active loans.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="panel">
                        <div class="panel-header">
                            <div><h2>Borrow History</h2><p>Recent historical loan records for auditing.</p></div>
                        </div>
                        <div class="panel-subtle">
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr><th>Borrower</th><th>Title</th><th>Borrowed</th><th>Returned</th><th>Status</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($historyItems as $item)
                                            <tr>
                                                <td><strong>{{ $item->borrower_name }}</strong></td>
                                                <td>{{ $item->title }}<div class="muted">{{ $item->isbn }}</div></td>
                                                <td>{{ $item->borrowed_at }}</td>
                                                <td>{{ $item->returned_at ?? 'Pending' }}</td>
                                                <td>
                                                    <span class="badge {{ $item->status === 'Returned' ? 'good' : ($item->status === 'Overdue' ? 'danger' : 'warn') }}">
                                                        {{ $item->status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="muted">No borrow history found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                @elseif ($page === 'activity')
                    <section class="panel">
                        <div class="panel-header">
                            <div>
                                <h2>Activity Log</h2>
                                <p>Filter recent system actions, including logins and record changes.</p>
                            </div>
                        </div>

                        <form class="toolbar" method="GET" action="{{ route('admin.activity') }}">
                            <input name="search" type="search" value="{{ $search }}" placeholder="Search action, subject, user, or details">
                            <select name="activity" aria-label="Filter by action">
                                <option value="">All actions</option>
                                @foreach (['login','logout','create_user','update_user_role','toggle_user_status','create_book','update_book','delete_book','create_announcement','update_announcement','delete_announcement'] as $action)
                                    <option value="{{ $action }}" @selected($activityFilter === $action)>{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                                @endforeach
                            </select>
                            <select name="activity_role" aria-label="Filter by role">
                                <option value="">All roles</option>
                                @foreach ($roleOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($activityRoleFilter === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <input name="activity_date_from" type="date" value="{{ $activityDateFrom }}" aria-label="Activity date from">
                            <input name="activity_date_to" type="date" value="{{ $activityDateTo }}" aria-label="Activity date to">
                            <button class="btn btn-green" type="submit">Filter</button>
                            <a class="action-link secondary" href="{{ route('admin.activity') }}">Reset</a>
                        </form>

                        <div class="panel-subtle">
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Description</th>
                                            <th>Subject</th>
                                            <th>Details</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($activityLogs as $log)
                                            @php $logDetails = json_decode($log->details ?? '{}', true) ?: []; @endphp
                                            <tr>
                                                <td>{{ $log->user_name ?? 'System' }}</td>
                                                <td><span class="badge">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span></td>
                                                <td>{{ $log->description ?? ucfirst(str_replace('_', ' ', $log->action)) }}</td>
                                                <td>{{ $log->subject_type ?? 'N/A' }}{{ $log->subject_id ? ' #'.$log->subject_id : '' }}</td>
                                                <td>
                                                    @if ($logDetails)
                                                        <div class="details-cell">
                                                            @foreach ($logDetails as $k => $v)
                                                                <span class="kv"><strong>{{ $k }}:</strong> {{ $v }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="muted">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ $log->created_at }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="muted">No activity found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                @elseif ($page === 'login-history')
                    <section class="panel">
                        <div class="panel-header">
                            <div>
                                <h2>Login History</h2>
                                <p>Review recent sign-ins from admins, librarians, instructors, students, and guests.</p>
                            </div>
                        </div>

                        <form class="toolbar" method="GET" action="{{ route('admin.login-history') }}">
                            <input name="search" type="search" value="{{ $search }}" placeholder="Search user, role, identifier, or details">
                            <select name="login_role" aria-label="Filter by role">
                                <option value="">All roles</option>
                                @foreach ($roleOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($loginRoleFilter === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <input name="login_date_from" type="date" value="{{ $loginDateFrom }}" aria-label="Login date from">
                            <input name="login_date_to" type="date" value="{{ $loginDateTo }}" aria-label="Login date to">
                            <button class="btn btn-green" type="submit">Search</button>
                            <a class="action-link secondary" href="{{ route('admin.login-history') }}">Reset</a>
                        </form>

                        <div class="panel-subtle">
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr><th>User</th><th>Role</th><th>Identifier</th><th>Details</th><th>Time</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($loginHistoryLogs as $log)
                                            @php $loginDetails = json_decode($log->details ?? '{}', true) ?: []; @endphp
                                            <tr>
                                                <td>{{ $log->user_name ?? 'System' }}</td>
                                                <td>{{ $loginDetails['role'] ?? 'N/A' }}</td>
                                                <td>{{ $loginDetails['identifier'] ?? 'N/A' }}</td>
                                                <td>
                                                    @if ($loginDetails)
                                                        <div class="details-cell">
                                                            @foreach ($loginDetails as $k => $v)
                                                                <span class="kv"><strong>{{ $k }}:</strong> {{ $v }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="muted">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ $log->created_at }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="muted">No login history found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                @elseif ($page === 'announcements')
                    <section class="panel section-anchor" id="announcements">
                        <div class="panel-header">
                            <div>
                                <h2>Announcements</h2>
                                <p>Publish and maintain notices for the library community.</p>
                            </div>
                        </div>

                        <form class="toolbar" method="GET" action="{{ route('admin.announcements') }}">
                            <input name="search" type="search" value="{{ $search }}" placeholder="Search title, body, or audience">
                            <select name="audience" aria-label="Filter by audience">
                                <option value="">All audiences</option>
                                @foreach ($audienceOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($audienceFilter === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-green" type="submit">Filter</button>
                            <a class="action-link secondary" href="{{ route('admin.announcements') }}">Reset</a>
                        </form>

                        <div class="stack-form">
                            <div class="stack-card">
                                <h3>Publish Announcement</h3>
                                <p>Create a new announcement for a specific audience or for everyone.</p>
                                <form method="POST" action="{{ route('admin.announcements.store') }}">
                                    @csrf
                                    <div class="form-grid">
                                        <div class="field">
                                            <label>Title</label>
                                            <input name="title" type="text" placeholder="Announcement title" required>
                                        </div>
                                        <div class="field">
                                            <label>Audience</label>
                                            <select name="audience" required>
                                                @foreach ($audienceOptions as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label>Published At</label>
                                            <input name="published_at" type="date" value="{{ now()->toDateString() }}">
                                        </div>
                                        <div class="field">
                                            <label>Status</label>
                                            <select name="status" required>
                                                @foreach ($announcementStatuses as $statusOption)
                                                    <option value="{{ $statusOption }}" @selected($statusOption === 'Published')>{{ $statusOption }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="field full">
                                            <label>Body</label>
                                            <textarea name="body" placeholder="Announcement details" required></textarea>
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

                @elseif ($page === 'fines')
                    <section class="hero">
                        <span class="eyebrow">Fines Management</span>
                        <h1>Overdue Fines</h1>
                        <p>Review, collect, and waive fines. Rate is ₱5.00 per overdue day.</p>
                        <div class="hero-actions">
                            <form method="POST" action="{{ route('admin.fines.recalculate') }}">
                                @csrf
                                <button class="btn btn-green" type="submit">Recalculate All Fines</button>
                            </form>
                        </div>
                    </section>

                    <section class="status-grid">
                        <div class="status-card red">
                            <span class="label">Unpaid Fines</span>
                            <span class="count">₱{{ number_format($stats['fines_unpaid'] ?? 0, 2) }}</span>
                            <p>Total outstanding amount to collect.</p>
                        </div>
                        <div class="status-card green">
                            <span class="label">Collected</span>
                            <span class="count">₱{{ number_format($stats['fines_collected'] ?? 0, 2) }}</span>
                            <p>Total fines paid to date.</p>
                        </div>
                    </section>

                    <section class="panel">
                        <div class="panel-header">
                            <div><h2>All Fines</h2><p>Mark fines as paid or waived from here.</p></div>
                        </div>
                        <div class="export-bar">
                            <span>Export:</span>
                            <a class="btn action-link secondary" href="{{ route('admin.fines.export', ['format'=>'csv']) }}">CSV</a>
                            <a class="btn action-link secondary" href="{{ route('admin.fines.export', ['format'=>'pdf']) }}">PDF</a>
                        </div>
                        <form class="toolbar" method="GET" action="{{ route('admin.fines') }}">
                            <select name="status" aria-label="Filter by status">
                                <option value="">All statuses</option>
                                <option value="Unpaid"  @selected(($fineStatusFilter??'') === 'Unpaid')>Unpaid</option>
                                <option value="Paid"    @selected(($fineStatusFilter??'') === 'Paid')>Paid</option>
                                <option value="Waived"  @selected(($fineStatusFilter??'') === 'Waived')>Waived</option>
                            </select>
                            <button class="btn btn-green" type="submit">Filter</button>
                            <a class="action-link secondary" href="{{ route('admin.fines') }}">Reset</a>
                        </form>
                        <div class="panel-subtle">
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr><th>Borrower</th><th>Book</th><th>Overdue Days</th><th>Amount</th><th>Due Date</th><th>Status</th><th>Actions</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($fines ?? [] as $fine)
                                            <tr>
                                                <td><strong>{{ $fine->borrower_name }}</strong><div class="muted">{{ $fine->borrower_id }}</div></td>
                                                <td>{{ $fine->title }}<div class="muted">{{ $fine->isbn }}</div></td>
                                                <td>{{ $fine->overdue_days }} days</td>
                                                <td><span class="fine-amount">₱{{ number_format($fine->amount, 2) }}</span></td>
                                                <td>{{ $fine->due_at }}</td>
                                                <td>
                                                    <span class="badge {{ $fine->status === 'Paid' ? 'good' : ($fine->status === 'Waived' ? 'warn' : 'danger') }}">
                                                        {{ $fine->status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($fine->status === 'Unpaid')
                                                        <div class="actions">
                                                            <form method="POST" action="{{ route('admin.fines.pay', $fine->id) }}">
                                                                @csrf @method('PATCH')
                                                                <button class="btn btn-green" type="submit">Mark Paid</button>
                                                            </form>
                                                            <form method="POST" action="{{ route('admin.fines.waive', $fine->id) }}">
                                                                @csrf @method('PATCH')
                                                                <button class="btn btn-blue" type="submit">Waive</button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <span class="muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="muted">No fines found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                @elseif ($page === 'reviews')
                    <section class="panel">
                        <div class="panel-header">
                            <div><h2>Book Ratings &amp; Reviews</h2><p>See what patrons think about the collection.</p></div>
                        </div>
                        <div class="export-bar">
                            <span>Export:</span>
                            <a class="btn action-link secondary" href="{{ route('admin.reviews.export', ['format'=>'csv']) }}">CSV</a>
                        </div>
                        <div class="panel-subtle">
                            <h3 style="color:var(--green-deep); margin:clamp(0.85rem, 1.6vw, 1rem) 0 clamp(0.4rem, 0.8vw, 0.55rem);">Top Rated Books</h3>
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr><th>Title</th><th>Author</th><th>Genre</th><th>Avg Rating</th><th>Reviews</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($topRated ?? [] as $book)
                                            <tr>
                                                <td><strong>{{ $book->title }}</strong></td>
                                                <td>{{ $book->author }}</td>
                                                <td>{{ $book->genre }}</td>
                                                <td>
                                                    <div class="stars">
                                                        @for ($s = 1; $s <= 5; $s++)
                                                            <span class="star {{ $s <= round($book->avg_rating) ? '' : 'empty' }}">★</span>
                                                        @endfor
                                                    </div>
                                                    <span class="muted" style="font-size:clamp(0.74rem, 0.3vw + 0.66rem, 0.85rem);">{{ $book->avg_rating }}/5</span>
                                                </td>
                                                <td>{{ $book->review_count }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="muted">No reviews yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <h3 style="color:var(--green-deep); margin:clamp(1rem, 2vw, 1.5rem) 0 clamp(0.4rem, 0.8vw, 0.55rem);">All Reviews</h3>
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr><th>Reviewer</th><th>Book</th><th>Rating</th><th>Review</th><th>Date</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($allReviews ?? [] as $review)
                                            <tr>
                                                <td><strong>{{ $review->reviewer_name }}</strong></td>
                                                <td>{{ $review->title }}<div class="muted">{{ $review->isbn }}</div></td>
                                                <td>
                                                    <div class="stars">
                                                        @for ($s = 1; $s <= 5; $s++)
                                                            <span class="star {{ $s <= $review->rating ? '' : 'empty' }}">★</span>
                                                        @endfor
                                                    </div>
                                                </td>
                                                <td>{{ $review->review ?? '—' }}</td>
                                                <td>{{ $review->created_at }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="muted">No reviews yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                @elseif ($page === 'notifications')
                    <section class="panel">
                        <div class="panel-header">
                            <div>
                                <h2>Notifications</h2>
                                <p>System alerts for overdue books, fines, reservations, and announcements.</p>
                            </div>
                            <form method="POST" action="{{ route('admin.notifications.read') }}">
                                @csrf
                                <button class="btn btn-green" type="submit">Mark All Read</button>
                            </form>
                        </div>
                        <div class="panel-subtle" style="padding-top:clamp(1rem, 2vw, 1.2rem);">
                            <div class="notif-list">
                                @forelse ($adminNotifications ?? [] as $notif)
                                    <div class="notif-item {{ $notif->read_at ? '' : 'unread' }}">
                                        <span class="notif-icon">
                                            {{ match($notif->type) {
                                                'overdue'             => '⚠️',
                                                'fine'                => '💰',
                                                'reservation_ready'   => '📚',
                                                'announcement'        => '📢',
                                                'return_reminder'     => '🔔',
                                                default               => '🔔',
                                            } }}
                                        </span>
                                        <div class="notif-body">
                                            <strong>{{ $notif->title }}</strong>
                                            <span>{{ $notif->body }}</span>
                                            <span style="display:block; margin-top:clamp(0.25rem, 0.7vw, 0.35rem); font-size:clamp(0.7rem, 0.3vw + 0.63rem, 0.82rem);">
                                                Type: {{ $notif->type }} | Created: {{ $notif->created_at }}
                                                @if ($notif->read_at)
                                                    | Read: {{ $notif->read_at }}
                                                @endif
                                            </span>
                                            @if (! $notif->read_at)
                                                <form method="POST" action="{{ route('admin.notifications.read.one', $notif->id) }}" style="margin-top:clamp(0.4rem, 0.9vw, 0.55rem);">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-blue" type="submit">Mark Read</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="muted">No notifications yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>

                @elseif ($page === 'charts')
                    <section class="hero">
                        <span class="eyebrow">Visual Reports</span>
                        <h1>Library at a Glance</h1>
                        <p>Bar charts for user distribution, genre popularity, circulation, and fines. Pick a month to focus the borrowing data.</p>
                    </section>
                    <div class="hero-actions" style="margin-top:clamp(-0.2rem, -0.3vw, -0.1rem);">
                        <form class="toolbar" method="GET" action="{{ route('admin.charts') }}" style="padding:0; width:100%; margin:0;">
                            <select name="month" aria-label="Select month">
                                @foreach ($chartMonths as $monthOption)
                                    <option value="{{ $monthOption['value'] }}" @selected(($chartMonth ?? now()->format('Y-m')) === $monthOption['value'])>{{ $monthOption['label'] }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-green" type="submit">View Month</button>
                            <a class="action-link secondary" href="{{ route('admin.reports.export', ['type' => 'charts', 'month' => $chartMonth ?? now()->format('Y-m')]) }}">Export CSV</a>
                        </form>
                    </div>

                    @php
                        $userCounts  = $stats['users'] ?? [];
                        $roleKeys    = ['admin', 'librarian', 'instructor', 'student', 'guest'];
                        $roleCounts  = array_intersect_key($userCounts, array_flip($roleKeys));
                        $totalUsers  = max(1, array_sum($roleCounts));
                        $genreStats  = $genreStats ?? collect();
                        $maxGenre    = max(1, $genreStats->max('count') ?? 1);
                        $topBookMax       = max(1, $topBorrowedBooks->max('borrow_count') ?? 1);
                        $borrowedGenreMax = max(1, $borrowedGenreStats->max('borrow_count') ?? 1);
                        $trendMax         = max(1, $borrowTrend->max('borrow_count') ?? 1);
                    @endphp

                    <div class="chart-grid">
                        <div class="chart-box">
                            <h3>Users by Role</h3>
                            <div class="bar-chart">
                                @foreach (['admin'=>'blue','librarian'=>'green','instructor'=>'gold','student'=>'orange','guest'=>'red'] as $role => $color)
                                    @php $count = $userCounts[$role] ?? 0; @endphp
                                    <div class="bar-row">
                                        <span>{{ ucfirst($role) }}</span>
                                        <div class="bar-track"><div class="bar-fill {{ $color }}" data-width="{{ $totalUsers > 0 ? round($count/$totalUsers*100) : 0 }}"></div></div>
                                        <span class="bar-val">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="chart-box">
                            <h3>Books by Genre</h3>
                            <div class="bar-chart">
                                @forelse ($genreStats as $g)
                                    <div class="bar-row">
                                        <span class="chart-label">{{ $g->genre }}</span>
                                        <div class="bar-track"><div class="bar-fill" data-width="{{ round($g->count/$maxGenre*100) }}"></div></div>
                                        <span class="bar-val">{{ $g->count }}</span>
                                    </div>
                                @empty
                                    <p class="muted">No data.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="chart-box">
                            <h3>Circulation Summary</h3>
                            <div class="bar-chart">
                                @php
                                    $circMax = max(1, $stats['loans'] ?? 0, $stats['reservations'] ?? 0, $stats['overdue'] ?? 0);
                                @endphp
                                <div class="bar-row"><span>Active Loans</span><div class="bar-track"><div class="bar-fill gold" data-width="{{ round(($stats['loans']??0)/$circMax*100) }}"></div></div><span class="bar-val">{{ $stats['loans']??0 }}</span></div>
                                <div class="bar-row"><span>Reservations</span><div class="bar-track"><div class="bar-fill orange" data-width="{{ round(($stats['reservations']??0)/$circMax*100) }}"></div></div><span class="bar-val">{{ $stats['reservations']??0 }}</span></div>
                                <div class="bar-row"><span>Overdue</span><div class="bar-track"><div class="bar-fill red" data-width="{{ round(($stats['overdue']??0)/$circMax*100) }}"></div></div><span class="bar-val">{{ $stats['overdue']??0 }}</span></div>
                            </div>
                        </div>

                        <div class="chart-box">
                            <h3>Fines Summary</h3>
                            <div class="bar-chart">
                                @php
                                    $finesMax = max(1, $stats['fines_unpaid']??0, $stats['fines_collected']??0);
                                @endphp
                                <div class="bar-row"><span>Collected</span><div class="bar-track"><div class="bar-fill" data-width="{{ round(($stats['fines_collected']??0)/$finesMax*100) }}"></div></div><span class="bar-val">₱{{ number_format($stats['fines_collected']??0,0) }}</span></div>
                                <div class="bar-row"><span>Unpaid</span><div class="bar-track"><div class="bar-fill red" data-width="{{ round(($stats['fines_unpaid']??0)/$finesMax*100) }}"></div></div><span class="bar-val">₱{{ number_format($stats['fines_unpaid']??0,0) }}</span></div>
                            </div>
                        </div>

                        <div class="chart-box">
                            <h3>Most Borrowed Books - {{ $chartMonthLabel ?? now()->format('F Y') }}</h3>
                            <div class="bar-chart">
                                @forelse ($topBorrowedBooks as $book)
                                    <div class="bar-row">
                                        <span class="chart-label">{{ $book->title }}</span>
                                        <div class="bar-track"><div class="bar-fill gold" data-width="{{ round($book->borrow_count/$topBookMax*100) }}"></div></div>
                                        <span class="bar-val">{{ $book->borrow_count }}</span>
                                    </div>
                                @empty
                                    <p class="muted">No borrow data for this month.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="chart-box">
                            <h3>Borrowed Genres - {{ $chartMonthLabel ?? now()->format('F Y') }}</h3>
                            <div class="bar-chart">
                                @forelse ($borrowedGenreStats as $genre)
                                    <div class="bar-row">
                                        <span class="chart-label">{{ $genre->genre }}</span>
                                        <div class="bar-track"><div class="bar-fill blue" data-width="{{ round($genre->borrow_count/$borrowedGenreMax*100) }}"></div></div>
                                        <span class="bar-val">{{ $genre->borrow_count }}</span>
                                    </div>
                                @empty
                                    <p class="muted">No borrow data for this month.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="chart-box">
                            <h3>Borrow Trend - Last 6 Months</h3>
                            <div class="bar-chart">
                                @forelse ($borrowTrend as $trend)
                                    <div class="bar-row">
                                        <span class="chart-label">{{ \Carbon\Carbon::parse($trend->month.'-01')->format('M Y') }}</span>
                                        <div class="bar-track"><div class="bar-fill red" data-width="{{ round($trend->borrow_count/$trendMax*100) }}"></div></div>
                                        <span class="bar-val">{{ $trend->borrow_count }}</span>
                                    </div>
                                @empty
                                    <p class="muted">No trend data available.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </main>
    </div>

    <div class="modal" id="userCreateModal" aria-hidden="true">
        <div class="modal-box">
            <div class="modal-head">
                <div>
                    <h3>Create User</h3>
                    <p class="muted">Add a new account for a librarian, instructor, or student.</p>
                </div>
                <button class="modal-close" type="button" onclick="closeModal('userCreateModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="form-grid">
                        <div class="field">
                            <label>Name</label>
                            <input name="name" type="text" placeholder="Full name" required>
                        </div>
                        <div class="field">
                            <label>Email</label>
                            <input name="email" type="email" placeholder="user@isu.edu.ph" required>
                        </div>
                        <div class="field">
                            <label>Login ID</label>
                            <input name="login_id" type="text" placeholder="Optional login ID">
                        </div>
                        <div class="field">
                            <label>Role</label>
                            <select name="role" required>
                                <option value="">Select role</option>
                                @foreach ($createRoleOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Password</label>
                            <input name="password" type="password" placeholder="Set a password" required>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <button class="btn btn-green" type="submit">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <<div class="modal" id="bookCreateModal" aria-hidden="true">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h3>Add Book</h3>
                <p class="muted">Create a new catalog record. Available copies default to the quantity you enter.</p>
            </div>
            <button class="modal-close" type="button" onclick="closeModal('bookCreateModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="bookCreateForm" method="POST" action="{{ route('admin.books.store') }}">
                @csrf
                <div class="form-grid">
                    
                    <div class="field full" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div>
                            <label for="book-create-isbn">ISBN</label>
                            <div style="display: flex; gap: clamp(.6rem, 1.2vw, .75rem); flex-wrap: wrap; align-items: center; margin-top: 0.4rem;">
                                <input id="book-create-isbn" name="isbn" type="text" inputmode="numeric" autocomplete="off" placeholder="978-0132350884" required style="flex: 1; min-width: 0;">
                                <button class="btn btn-green" type="button" id="openBarcodeScanner">Scan Barcode</button>
                            </div>
                            <p class="muted" style="margin-top: clamp(.4rem, 1vw, .6rem);">Scan an EAN-13 barcode to fetch the book data automatically.</p>
                        </div>
                        
                        <div style="padding: 1.25rem; border: 1px solid var(--line); border-radius: 0.9rem; background: rgba(245,250,242,0.8); display: flex; flex-direction: column; gap: 0.75rem;">
                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                <p style="margin: 0; font-weight: 700; color: var(--green-deep);">Scan with Phone</p>
                                <p class="muted" style="margin: 0; font-size: 0.85rem;">Open this link on your phone to scan the barcode remotely.</p>
                            </div>
                            
                            <div style="display: flex; gap: 0.6rem; flex-wrap: wrap; align-items: center;">
                                <input type="text" readonly value="{{ $scanPhoneUrl ?? '' }}" style="flex: 1; min-width: 200px; font-size: 0.85rem;" aria-label="Phone scan link">
                                <a class="btn btn-green" href="{{ $scanPhoneUrl ?? '#' }}" target="_blank" rel="noopener" style="font-size: 0.85rem; padding: 0.5rem 0.75rem;">Open on Phone</a>
                            </div>
                            
                            <div id="adminPhoneScanQr" style="display: flex; justify-content: center; margin-top: 0.25rem;"></div>
                            <p id="adminPhoneScanStatus" class="muted" style="margin: 0; text-align: center; font-size: 0.82rem;"></p>
                        </div>
                    </div>

                    <div class="field">
                        <label for="book-create-title">Title</label>
                        <input id="book-create-title" name="title" type="text" placeholder="Book title" required>
                    </div>
                    <div class="field">
                        <label for="book-create-author">Author</label>
                        <input id="book-create-author" name="author" type="text" placeholder="Author name" required>
                    </div>
                    <div class="field">
                        <label for="book-create-genre">Genre</label>
                        <input id="book-create-genre" name="genre" list="genreOptions" type="text" placeholder="Select or type a genre" required>
                    </div>
                    <div class="field">
                        <label for="book-create-year">Year Published</label>
                        <input id="book-create-year" name="year_published" type="number" min="1000" max="9999" placeholder="2024">
                    </div>
                    <div class="field">
                        <label for="book-create-quantity">Quantity</label>
                        <input id="book-create-quantity" name="quantity" type="number" min="0" value="1" required>
                    </div>
                    <div class="field">
                        <label for="book-create-location">Location</label>
                        <input id="book-create-location" name="location" type="text" placeholder="Shelf A1, Floor 2">
                    </div>
                    <div class="field full">
                        <label for="book-create-description">Description</label>
                        <textarea id="book-create-description" name="description" placeholder="Optional book description"></textarea>
                    </div>
                </div>
                <div class="hero-actions">
                    <button class="btn btn-green" type="submit">Save Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <div class="modal" id="barcodeScannerModal" aria-hidden="true">
        <div class="modal-box" style="max-width: min(45rem, 100%);">
            <div class="modal-head">
                <div>
                    <h3>Scan Barcode</h3>
                    <p class="muted">Point your phone camera at a book barcode. EAN-13 and EAN-8 are supported.</p>
                </div>
                <button class="modal-close" type="button" id="closeBarcodeScanner">&times;</button>
            </div>
            <div class="modal-body">
                <div id="barcodeReader" style="width:100%; min-height: clamp(16rem, 35vw, 20rem); border: 1px dashed rgba(255,255,255,.18); border-radius: 1rem; overflow: hidden; background: rgba(255,255,255,.03);"></div>
                <p id="barcodeScannerStatus" class="muted" style="margin-top: clamp(.6rem, 1.2vw, .85rem);">Allow camera access when prompted. We will stop the feed as soon as a barcode is detected.</p>
            </div>
        </div>
    </div>

    <div class="modal" id="bookModal" aria-hidden="true">
        <div class="modal-box">
            <div class="modal-head">
                <div>
                    <h3>Edit Book</h3>
                    <p class="muted">Update catalog details, quantity, or status.</p>
                </div>
                <button class="modal-close" type="button" onclick="closeModal('bookModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="bookModalForm">
                    @csrf
                    @method('PATCH')
                    <div class="form-grid">
                        <div class="field">
                            <label>ISBN</label>
                            <input name="isbn" type="text" readonly>
                        </div>
                        <div class="field">
                            <label>Title</label>
                            <input name="title" type="text" required>
                        </div>
                        <div class="field">
                            <label>Author</label>
                            <input name="author" type="text" required>
                        </div>
                        <div class="field">
                            <label>Genre</label>
                            <input name="genre" list="genreOptions" type="text" placeholder="Select or type a genre" required>
                        </div>
                        <div class="field">
                            <label>Year Published</label>
                            <input name="year_published" type="number" min="1000" max="9999">
                        </div>
                        <div class="field">
                            <label>Quantity</label>
                            <input name="quantity" id="editQty" type="number" min="0" required oninput="capAvailable()">
                        </div>
                        <div class="field">
                            <label>Available Quantity</label>
                            <input name="available_quantity" id="editAvailQty" type="number" min="0" required oninput="capAvailable()">
                        </div>
                        <div class="field">
                            <label>Location</label>
                            <input name="location" type="text" placeholder="Shelf A1, Floor 2">
                        </div>
                        <div class="field">
                            <label>Status</label>
                            <select name="status" required>
                                @foreach ($bookStatuses as $statusOption)
                                    <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field full">
                            <label>Description</label>
                            <textarea name="description"></textarea>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <button class="btn btn-green" type="submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="announcementModal" aria-hidden="true">
        <div class="modal-box">
            <div class="modal-head">
                <div>
                    <h3>Edit Announcement</h3>
                    <p class="muted">Adjust the notice content, audience, or publication status.</p>
                </div>
                <button class="modal-close" type="button" onclick="closeModal('announcementModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="announcementModalForm">
                    @csrf
                    @method('PATCH')
                    <div class="form-grid">
                        <div class="field">
                            <label>Title</label>
                            <input name="title" type="text" required>
                        </div>
                        <div class="field">
                            <label>Audience</label>
                            <select name="audience" required>
                                @foreach ($audienceOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Published At</label>
                            <input name="published_at" type="date">
                        </div>
                        <div class="field">
                            <label>Status</label>
                            <select name="status" required>
                                @foreach ($announcementStatuses as $statusOption)
                                    <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field full">
                            <label>Body</label>
                            <textarea name="body" required></textarea>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <button class="btn btn-green" type="submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
            const openModal = (id) => {
                const el = document.getElementById(id);
                if (el) { el.classList.add('open'); el.setAttribute('aria-hidden', 'false'); }
                if (id === 'bookCreateModal') {
                    const token = @json($scanToken ?? null);
                    if (!token) return;
                    const qrContainer = document.getElementById('adminPhoneScanQr');
                    const statusEl = document.getElementById('adminPhoneScanStatus');
                    if (qrContainer && window.QRCode) {
                        qrContainer.innerHTML = '';
                        new QRCode(qrContainer, {
                            text: `{{ url('/scan') }}/${encodeURIComponent(token)}`,
                            width: 160,
                            height: 160,
                        });
                    }
                    if (statusEl) statusEl.textContent = 'Scan the QR code with your phone or use the link above.';
                    if (!adminScanPollTimer && token) {
                        adminScanPollTimer = window.setInterval(async () => {
                            try {
                                const response = await fetch(`{{ url('/scan') }}/${encodeURIComponent(token)}/status`, {
                                    headers: { 'Accept': 'application/json' }
                                });
                                const data = await response.json();
                                if (data.status !== 'scanned' || !data.isbn) return;
                                clearInterval(adminScanPollTimer);
                                adminScanPollTimer = null;
                                if (statusEl) statusEl.textContent = `Phone scan received: ${data.isbn}. Fetching book data...`;
                                await lookupAndFill(data.isbn);
                                if (statusEl) statusEl.textContent = 'Book data filled from phone scan.';
                            } catch (error) {
                                console.warn('Admin phone scan polling failed', error);
                            }
                        }, 2000);
                    }
                }
            };
        const closeModal = (id) => {
            const el = document.getElementById(id);
            if (el) { el.classList.remove('open'); el.setAttribute('aria-hidden', 'true'); }
        };

        const openBookModal = (book) => {
            const form = document.getElementById('bookModalForm');
            form.action = `{{ url('/dashboard/admin/books') }}/` + encodeURIComponent(book.isbn);
            form.querySelector('[name="isbn"]').value               = book.isbn               ?? '';
            form.querySelector('[name="title"]').value              = book.title              ?? '';
            form.querySelector('[name="author"]').value             = book.author             ?? '';
            form.querySelector('[name="genre"]').value              = book.genre              ?? '';
            form.querySelector('[name="year_published"]').value     = book.year               ?? '';
            form.querySelector('[name="quantity"]').value           = book.quantity           ?? 0;
            form.querySelector('[name="available_quantity"]').value = book.available_quantity ?? 0;
            form.querySelector('[name="location"]').value           = book.location           ?? '';
            form.querySelector('[name="status"]').value             = book.status             ?? 'Available';
            form.querySelector('[name="description"]').value        = book.description        ?? '';
            openModal('bookModal');
        };

        const capAvailable = () => {
            const qty      = parseInt(document.getElementById('editQty').value)      || 0;
            const availEl  = document.getElementById('editAvailQty');
            const avail    = parseInt(availEl.value) || 0;
            availEl.max    = qty;
            if (avail > qty) availEl.value = qty;
        };

        const isbnInput = document.getElementById('book-create-isbn');
        const bookCreateForm = document.getElementById('bookCreateForm');
        const openBarcodeScannerBtn = document.getElementById('openBarcodeScanner');
        const closeBarcodeScannerBtn = document.getElementById('closeBarcodeScanner');
        const barcodeScannerModal = document.getElementById('barcodeScannerModal');
        const barcodeScannerStatus = document.getElementById('barcodeScannerStatus');
        const barcodeReaderId = 'barcodeReader';
        let barcodeScanner = null;
        let barcodeScannerStopping = false;

        const fillBookForm = (data = {}) => {
            if (!bookCreateForm) return;
            const mappings = {
                isbn: data.isbn,
                title: data.title,
                author: data.author,
                genre: data.genre,
                year_published: data.year_published,
                description: data.description,
            };
            Object.entries(mappings).forEach(([name, value]) => {
                const input = bookCreateForm.querySelector(`[name="${name}"]`);
                if (!input || value === undefined || value === null || value === '') return;
                input.value = value;
            });
            const quantityInput = bookCreateForm.querySelector('[name="quantity"]');
            if (quantityInput && !quantityInput.value) {
                quantityInput.value = 1;
            }
        };

        const fetchBookMetadata = async (isbn) => {
            const response = await fetch(`{{ url('/books/lookup') }}/${encodeURIComponent(isbn)}`, {
                headers: { 'Accept': 'application/json' },
            });
            if (!response.ok) return null;
            return await response.json();
        };

        const extractIsbn = (text) => (text || '').replace(/[^0-9Xx]/g, '');

        const fillBookFormFromMetadata = (isbn, metadata) => {
            fillBookForm({
                isbn,
                title: metadata?.title || '',
                author: metadata?.author || '',
                genre: metadata?.genre || '',
                year_published: metadata?.year_published || '',
            });
        };

        const lookupAndFill = async (isbn) => {
            const normalized = extractIsbn(isbn);
            if (normalized.length < 8) return;
            if (isbnInput) isbnInput.value = normalized;
            const metadata = await fetchBookMetadata(normalized);
            if (!metadata) return;
            fillBookFormFromMetadata(normalized, metadata);
        };

        if (isbnInput) {
            isbnInput.addEventListener('input', async () => {
                const normalized = extractIsbn(isbnInput.value);
                if (normalized.length < 8) return;
                await lookupAndFill(normalized);
            });
        }

        const closeBarcodeScanner = async () => {
            barcodeScannerStopping = true;
            try {
                if (barcodeScanner) {
                    await barcodeScanner.clear();
                }
            } catch (error) {
                console.warn('Barcode scanner stop failed', error);
            } finally {
                barcodeScanner = null;
                barcodeScannerStopping = false;
                if (barcodeScannerModal) closeModal('barcodeScannerModal');
            }
        };

        const startBarcodeScanner = async () => {
            if (!window.Html5QrcodeScanner) {
                barcodeScannerStatus.textContent = 'Barcode scanner library failed to load.';
                return;
            }
            openModal('barcodeScannerModal');
            barcodeScannerStatus.textContent = 'Starting camera...';
            if (barcodeScanner) await closeBarcodeScanner();
            const supportedFormats = window.Html5QrcodeSupportedFormats
                ? [window.Html5QrcodeSupportedFormats.EAN_13, window.Html5QrcodeSupportedFormats.EAN_8]
                : undefined;
            barcodeScanner = new Html5QrcodeScanner(
                barcodeReaderId,
                { fps: 12, qrbox: { width: 320, height: 140 }, aspectRatio: 2.3, disableFlip: false, rememberLastUsedCamera: true, formatsToSupport: supportedFormats },
                false
            );
            await barcodeScanner.render(
                async (decodedText) => {
                    const isbn = extractIsbn(decodedText);
                    if (!isbn || barcodeScannerStopping) return;
                    barcodeScannerStatus.textContent = `Barcode detected: ${isbn}. Fetching book data...`;
                    await lookupAndFill(isbn);
                    await closeBarcodeScanner();
                },
                (scanError) => {
                    if (scanError) barcodeScannerStatus.textContent = 'Point the camera at a barcode and hold steady.';
                }
            );
        };

        openBarcodeScannerBtn?.addEventListener('click', startBarcodeScanner);
        closeBarcodeScannerBtn?.addEventListener('click', closeBarcodeScanner);
        barcodeScannerModal?.addEventListener('click', (event) => {
            if (event.target === barcodeScannerModal) closeBarcodeScanner();
        });

        const openAnnouncementModal = (a) => {
            const form = document.getElementById('announcementModalForm');
            form.action = `{{ url('/dashboard/admin/announcements') }}/` + encodeURIComponent(a.id);
            form.querySelector('[name="title"]').value        = a.title        ?? '';
            form.querySelector('[name="audience"]').value     = a.audience     ?? 'public';
            form.querySelector('[name="published_at"]').value = a.published_at ?? '';
            form.querySelector('[name="status"]').value       = a.status       ?? 'Published';
            form.querySelector('[name="body"]').value         = a.body         ?? '';
            openModal('announcementModal');
        };

        document.addEventListener('click', (e) => {
            const bookBtn = e.target.closest('.js-edit-book');
            if (bookBtn) {
                openBookModal(JSON.parse(bookBtn.dataset.book));
                return;
            }
            const announcementBtn = e.target.closest('.js-edit-announcement');
            if (announcementBtn) {
                openAnnouncementModal(JSON.parse(announcementBtn.dataset.announcement));
                return;
            }
        });

        document.querySelectorAll('.modal').forEach((el) => {
            el.addEventListener('click', (e) => {
                if (e.target === el) {
                    el.classList.remove('open');
                    el.setAttribute('aria-hidden', 'true');
                }
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.open').forEach((el) => {
                    el.classList.remove('open');
                    el.setAttribute('aria-hidden', 'true');
                });
            }
        });

        document.querySelectorAll('.bar-fill[data-width]').forEach((el) => {
            const width = Number(el.dataset.width || 0);
            el.style.width = `${Number.isFinite(width) ? width : 0}%`;
        });

        const adminScanToken = @json($scanToken ?? null);
        let adminScanPollTimer = null;

        const startAdminPhoneScanPolling = () => {
            if (!adminScanToken || adminScanPollTimer) return;
            const statusEl = document.getElementById('adminPhoneScanStatus');
            const qrContainer = document.getElementById('adminPhoneScanQr');
            if (statusEl) statusEl.textContent = 'Waiting for phone scan...';
            if (qrContainer && window.QRCode) {
                qrContainer.innerHTML = '';
                new QRCode(qrContainer, {
                    text: `{{ url('/scan') }}/${encodeURIComponent(adminScanToken)}`,
                    width: 160,
                    height: 160,
                });
            }
            adminScanPollTimer = window.setInterval(async () => {
                try {
                    const response = await fetch(`{{ url('/scan') }}/${encodeURIComponent(adminScanToken)}/status`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (data.status !== 'scanned' || !data.isbn) return;
                    clearInterval(adminScanPollTimer);
                    adminScanPollTimer = null;
                    if (statusEl) statusEl.textContent = `Phone scan received: ${data.isbn}. Fetching book data...`;
                    await lookupAndFill(data.isbn);
                    if (statusEl) statusEl.textContent = 'Book data filled from phone scan.';
                } catch (error) {
                    console.warn('Admin phone scan polling failed', error);
                }
            }, 2000);
        };

        document.getElementById('openBarcodeScanner')?.addEventListener('click', () => {
            startAdminPhoneScanPolling();
        });
        
        document.getElementById('bookCreateModal')?.addEventListener('click', (e) => {
            if (e.target === document.getElementById('bookCreateModal')) return;
            startAdminPhoneScanPolling();
        });

    </script>
</body>
</html>