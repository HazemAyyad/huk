<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>لوحة التحكم - إدارة المستخدمين</title>
    <link rel="icon" type="image/png" href="/files/telegram.png">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Tajawal', sans-serif;
}

:root {
    --primary: #4361ee;
    --primary-dark: #3a56d4;
    --primary-light: #eef2ff;
    --secondary: #7209b7;
    --secondary-light: #f3e8ff;
    --success: #10b981;
    --success-light: #d1fae5;
    --warning: #f59e0b;
    --warning-light: #fef3c7;
    --danger: #ef4444;
    --danger-light: #fee2e2;
    --info: #3b82f6;
    --info-light: #dbeafe;
    --light: #f8f9fa;
    --dark: #1f2937;
    --gray: #6b7280;
    --light-gray: #f3f4f6;
    --border-gray: #e5e7eb;
    --border-radius: 12px;
    --border-radius-sm: 8px;
    --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --box-shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

body {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    color: var(--dark);
    line-height: 1.6;
    min-height: 100vh;
    padding: 20px;
    text-rendering: optimizeLegibility;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.container {
    max-width: 1800px;
    margin: 0 auto;
    padding: 0 15px;
    overflow-x: hidden;
}

/* Header Styles */
.header {
    text-align: center;
    margin-bottom: 30px;
    padding: 35px 25px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border-radius: var(--border-radius);
    color: white;
    box-shadow: var(--box-shadow-lg);
    position: relative;
    overflow: hidden;
}

.header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
    height: 100%;
    background:
        radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%);
}

.header h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    position: relative;
    z-index: 1;
    font-weight: 700;
}

.header p {
    font-size: 1.2rem;
    opacity: 0.9;
    position: relative;
    z-index: 1;
    max-width: 800px;
    margin: 0 auto;
}

/* Sidebar Menu Styles */
.sidebar-menu-btn {
    position: fixed;
    top: 30px;
    right: 30px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 1;
    transform: translateY(0);
    z-index: 1000;
}

.sidebar-menu-btn.hidden {
    opacity: 0;
    transform: translateY(-100px);
    pointer-events: none;
}

.sidebar-menu-btn.visible {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
}

.scroll-top-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 999;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 0;
    transform: translateY(100px);
    font-size: 1.5rem;
    border: 2px solid rgba(255, 255, 255, 0.1);
}

.scroll-top-btn.visible {
    opacity: 1;
    transform: translateY(0);
}

.scroll-top-btn:hover {
    background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
    transform: translateY(-5px) scale(1.1);
    box-shadow: 0 15px 35px rgba(114, 9, 183, 0.4);
}

.scroll-sidebar-btn {
    position: fixed;
    top: 20px;
    right: 20px;
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 999;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 0;
    transform: translateY(-100px);
    font-size: 1.3rem;
}

.scroll-sidebar-btn.visible {
    opacity: 1;
    transform: translateY(0);
}

.scroll-sidebar-btn:hover {
    background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
    transform: scale(1.1);
}

.sidebar-menu-btn {
    position: fixed;
    top: 30px;
    right: 30px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border: none;
    border-radius: 16px;
    padding: 16px 28px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 1000;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 600;
    font-size: 1.1rem;
    overflow: hidden;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.1);
}

.sidebar-menu-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    z-index: 1;
}

.sidebar-menu-btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(114, 9, 183, 0.4);
    background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
}

.sidebar-menu-btn:active {
    transform: translateY(-2px);
}

.sidebar-menu-btn i {
    font-size: 1.3rem;
    z-index: 2;
}

.sidebar-menu-btn .btn-text {
    z-index: 2;
}

/* Sidebar Menu */
.sidebar-menu {
    position: fixed;
    top: 0;
    right: -420px;
    width: 400px;
    height: 100%;
    background: linear-gradient(180deg, #1a1f37 0%, #0f1328 100%);
    z-index: 2000;
    box-shadow: -10px 0 50px rgba(0, 0, 0, 0.3);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    backdrop-filter: blur(20px);
    border-left: 1px solid rgba(255, 255, 255, 0.05);
}

.sidebar-menu.active {
    right: 0;
}

.sidebar-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    padding: 35px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at 30% 70%, rgba(255,255,255,0.1) 0%, transparent 50%);
}

.sidebar-header-content {
    display: flex;
    align-items: center;
    gap: 20px;
    position: relative;
    z-index: 1;
}

.sidebar-header-content i {
    font-size: 2.2rem;
    color: white;
    background: rgba(255, 255, 255, 0.1);
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header h3 {
    color: white;
    font-size: 1.8rem;
    margin: 0;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.close-menu-btn {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.1);
    width: 45px;
    height: 45px;
    border-radius: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
    backdrop-filter: blur(5px);
}

.close-menu-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: rotate(90deg);
    border-color: rgba(255, 255, 255, 0.2);
}

.sidebar-content {
    flex: 1;
    padding: 30px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 35px;
}
/* وضع التحذير */
.modal.warning .modal-header {
    background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%) !important;
}

.modal.warning .modal-body {
    background: #fff5f5;
    color: #721c24;
}

.modal.warning .modal-footer {
    background: #f8d7da;
}

.modal.warning .modal-body strong {
    color: #dc3545;
}

.modal.warning .modal-content {
    border: 2px solid #dc3545;
}

.sidebar-content::-webkit-scrollbar {
    width: 6px;
}

.sidebar-content::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 3px;
}

.sidebar-content::-webkit-scrollbar-thumb {
    background: var(--primary);
    border-radius: 3px;
}

.sidebar-section-title {
    color: white;
    font-size: 1.2rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    padding-bottom: 10px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
}

.sidebar-section-title i {
    color: var(--primary);
    font-size: 1.1rem;
}

.sidebar-links {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid transparent;
    backdrop-filter: blur(10px);
}

.sidebar-link:hover {
    background: rgba(67, 97, 238, 0.15);
    transform: translateX(-10px);
    border-color: rgba(67, 97, 238, 0.3);
    box-shadow: 0 5px 15px rgba(67, 97, 238, 0.2);
}

.sidebar-link-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: white;
    flex-shrink: 0;
}

.sidebar-link-content {
    flex: 1;
}

.sidebar-link-title {
    display: block;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.sidebar-link-desc {
    display: block;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.6);
}

.sidebar-stats {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 20px;
    padding: 25px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.stats-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}

.sidebar-stat-card {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.3s ease;
    border: 1px solid transparent;
    backdrop-filter: blur(10px);
}

.sidebar-stat-card:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.1);
    transform: translateY(-5px);
}

.sidebar-stat-card .stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.sidebar-stat-card .stat-icon.total {
    background: linear-gradient(135deg, rgba(67, 97, 238, 0.2) 0%, rgba(67, 97, 238, 0.1) 100%);
    color: var(--primary);
}

.sidebar-stat-card .stat-icon.completed {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0.1) 100%);
    color: var(--success);
}

.sidebar-stat-card .stat-icon.blocked {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(239, 68, 68, 0.1) 100%);
    color: var(--danger);
}

.sidebar-stat-card .stat-icon.pending {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(245, 158, 11, 0.1) 100%);
    color: var(--warning);
}

.sidebar-stat-card .stat-info h4 {
    color: white;
    font-size: 1.5rem;
    margin: 0 0 5px 0;
    font-weight: 700;
}

.sidebar-stat-card .stat-info p {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.85rem;
    margin: 0;
}

.sidebar-footer {
    background: rgba(0, 0, 0, 0.2);
    padding: 25px 30px;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.sidebar-footer-content {
    display: flex;
    align-items: center;
    gap: 15px;
}

.sidebar-footer-content i {
    font-size: 2rem;
    color: var(--primary);
    background: rgba(67, 97, 238, 0.1);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(67, 97, 238, 0.2);
}

.footer-user {
    color: white;
    font-weight: 600;
    margin: 0 0 5px 0;
    font-size: 1.1rem;
}


.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 1999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.4s ease;
    backdrop-filter: blur(5px);
}

.sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Stats Cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 35px;
}

.stat-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 30px;
    display: flex;
    align-items: center;
    gap: 25px;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    border: 1px solid var(--border-gray);
    min-height: 150px;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 6px;
    height: 100%;
    transition: var(--transition);
}

.total::before { background: var(--primary); }
.completed::before { background: var(--success); }
.blocked::before { background: var(--danger); }
.pending::before { background: var(--warning); }

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--box-shadow-lg);
}

.stat-card:hover::before {
    width: 8px;
}

.stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    flex-shrink: 0;
    transition: var(--transition);
}

.stat-card:hover .stat-icon {
    transform: scale(1.1) rotate(5deg);
}

.completed .stat-icon {
    background: linear-gradient(135deg, var(--success-light) 0%, #a7f3d0 100%);
    color: var(--success);
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
}
.blocked .stat-icon {
    background: linear-gradient(135deg, var(--danger-light) 0%, #fecaca 100%);
    color: var(--danger);
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);
}
.pending .stat-icon {
    background: linear-gradient(135deg, var(--warning-light) 0%, #fde68a 100%);
    color: var(--warning);
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
}
.total .stat-icon {
    background: linear-gradient(135deg, var(--primary-light) 0%, #c7d2fe 100%);
    color: var(--primary);
    box-shadow: 0 4px 15px rgba(67, 97, 238, 0.2);
}

.stat-info h3 {
    font-size: 2.5rem;
    margin-bottom: 8px;
    font-weight: 700;
    line-height: 1;
}

.stat-info p {
    color: var(--gray);
    font-size: 1.1rem;
    font-weight: 500;
}

/* Message Section */
.message-section {
    background: white;
    border-radius: var(--border-radius);
    padding: 30px;
    box-shadow: var(--box-shadow);
    border: 1px solid var(--border-gray);
    margin-bottom: 35px;
    border-right: 6px solid var(--primary);
}

.message-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--light-gray);
}

.message-header i {
    font-size: 1.8rem;
    color: var(--primary);
    background: var(--primary-light);
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.message-header h2 {
    font-size: 1.5rem;
    color: var(--dark);
    margin: 0;
}

.message-controls {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
    background: var(--light-gray);
    padding: 20px;
    border-radius: var(--border-radius-sm);
    border: 1px solid var(--border-gray);
}

.control-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.control-group label {
    font-weight: 600;
    color: var(--dark);
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.control-group label i {
    color: var(--primary);
}

.control-group select {
    padding: 14px 18px;
    border: 2px solid var(--border-gray);
    border-radius: var(--border-radius-sm);
    font-size: 1rem;
    background: white;
    cursor: pointer;
    transition: var(--transition);
    font-family: 'Tajawal', sans-serif;
}

.control-group select:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.message-box-container {
    position: relative;
    margin-bottom: 20px;
}

.message-box {
    width: 100%;
    min-height: 140px;
    padding: 20px;
    border: 2px solid var(--border-gray);
    border-radius: var(--border-radius-sm);
    font-size: 1.05rem;
    resize: vertical;
    transition: var(--transition);
    font-family: 'Tajawal', sans-serif;
    line-height: 1.6;
    background: white;
}

.message-box:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.message-box::placeholder {
    color: var(--gray);
    opacity: 0.7;
}

.send-button {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    border: none;
    border-radius: var(--border-radius-sm);
    padding: 18px 30px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    transition: var(--transition);
    width: 100%;
    box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
}
/* لمسة إضافية للجمالية */
.transition-all {
    transition: all 0.3s ease;
}
.transition-all:hover {
    transform: translateY(-2px); /* رفعة بسيطة عند التمرير */
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3) !important;
}
.send-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
}

.send-button:active {
    transform: translateY(-1px);
}

/* Search and Table Container */
.search-table-container {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    border: 1px solid var(--border-gray);
    margin-bottom: 30px;
}

.search-section {
    background: linear-gradient(to bottom, #f8fafc, #f1f5f9);
    padding: 30px;
    border-bottom: 2px solid var(--border-gray);
}

.search-box {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

.search-box input {
    flex: 1;
    padding: 18px 22px;
    border: 2px solid var(--border-gray);
    border-radius: var(--border-radius-sm);
    font-size: 1.05rem;
    transition: var(--transition);
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.search-box input:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1), 0 2px 4px rgba(0, 0, 0, 0.1);
}

.search-btn {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    border: none;
    border-radius: var(--border-radius-sm);
    padding: 0 35px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: var(--transition);
    font-weight: 600;
    font-size: 1.05rem;
    box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
    min-width: 140px;
    justify-content: center;
}

.search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
}

.modal-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    margin: -40px -40px 20px -40px;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.3rem;
}

.modal-body {
    padding: 20px 0;
    font-size: 1.1rem;
    line-height: 1.6;
}

.modal-footer {
    padding: 20px 0 0 0;
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    border-top: 1px solid var(--border-gray);
    margin-top: 20px;
}

.filter-section {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: center;
    justify-content: space-between;
    padding-top: 20px;
    border-top: 2px dashed var(--border-gray);
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.filter-section select {
    padding: 14px 20px;
    border: 2px solid var(--border-gray);
    border-radius: var(--border-radius-sm);
    font-size: 1rem;
    background: white;
    cursor: pointer;
    min-width: 200px;
    transition: var(--transition);
    font-family: 'Tajawal', sans-serif;
}

.filter-section select:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.reset-btn {
    background: var(--light-gray);
    color: var(--dark);
    border: 2px solid var(--border-gray);
    border-radius: var(--border-radius-sm);
    padding: 14px 25px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: var(--transition);
    font-weight: 600;
    font-size: 1rem;
}

.reset-btn:hover {
    background: var(--primary-light);
    border-color: var(--primary);
    color: var(--primary);
}

/* Table Container - Desktop View */
.table-container {
    padding: 25px;
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    position: relative;
    background: white;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    table-layout: auto;
}

.users-table th:nth-child(1),
.users-table td:nth-child(1) { width: 50px; }

.users-table th:nth-child(2),
.users-table td:nth-child(2) { width: 200px; }

.users-table th:nth-child(3),
.users-table td:nth-child(3) { width: 120px; }

.users-table th:nth-child(4),
.users-table td:nth-child(4) { width: 120px; }

.users-table th:nth-child(5),
.users-table td:nth-child(5) { width: 100px; }

.users-table th:nth-child(6),
.users-table td:nth-child(6) { width: 120px; }

.users-table th:nth-child(7),
.users-table td:nth-child(7) { width: 120px; }


.users-table th:nth-child(8),
.users-table td:nth-child(8) { width: 250px; min-width: 250px; }

.users-table thead {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    position: sticky;
    top: 0;
    z-index: 10;
}

.users-table th {
    padding: 16px 12px;
    font-size: 0.9rem;
    text-align: right;
    font-weight: 600;
    position: relative;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border: none;
    transition: var(--transition);
}

.users-table th:first-child {
    border-top-right-radius: var(--border-radius-sm);
}

.users-table th:last-child {
    border-top-left-radius: var(--border-radius-sm);
}

.users-table th i {
    margin-right: 6px;
    opacity: 0.8;
    font-size: 0.8rem;
}

.users-table th:hover {
    background-color: rgba(255, 255, 255, 0.15);
    cursor: pointer;
}

.users-table td {
    padding: 14px 12px;
    text-align: right;
    color: var(--dark);
    font-size: 0.9rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--light-gray);
    transition: var(--transition);
    word-wrap: break-word;
    overflow-wrap: break-word;
    max-width: 100%;
}

.users-table tbody tr {
    transition: var(--transition);
}

.users-table tbody tr:hover {
    background-color: rgba(67, 97, 238, 0.05);
}

/* Full Name Styling - Improved for Arabic */
.full-name-complete {
    font-family: 'Tajawal', sans-serif;
    font-size: 1rem;
    line-height: 1.6;
    word-break: normal;
    overflow-wrap: anywhere;
    text-align: right;
    hyphens: auto;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    white-space: normal;
    word-wrap: break-word;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
    justify-content: center;
    transition: var(--transition);
}

.status-completed {
    background: var(--success-light);
    color: #047857;
    border: 1px solid #a7f3d0;
}

.status-blocked {
    background: var(--danger-light);
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.status-pending {
    background: var(--warning-light);
    color: #b45309;
    border: 1px solid #fde68a;
}

.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.btn {
    padding: 8px 12px;
    border: none;
    border-radius: var(--border-radius-sm);
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: var(--transition);
    white-space: nowrap;
    min-width: auto;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, var(--success) 0%, #0da271 100%);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
    color: white;
}

.btn-warning {
    background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
    color: white;
}

.btn-telegram {
    background: linear-gradient(135deg, #0088cc 0%, #006699 100%);
    color: white;
}

.btn-info {
    background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);
    color: white;
}

.btn-sm {
    padding: 6px 10px;
    font-size: 0.8rem;
}

.btn i {
    font-size: 0.8rem;
}

.user-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--primary);
    transition: var(--transition);
}

.no-results {
    text-align: center;
    padding: 60px 20px;
    color: var(--gray);
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: var(--border-radius);
    border: 3px dashed var(--border-gray);
}

.no-results i {
    font-size: 4rem;
    margin-bottom: 25px;
    color: #cbd5e1;
    opacity: 0.7;
}

.no-results h3 {
    font-size: 1.8rem;
    margin-bottom: 15px;
    color: var(--dark);
}

.no-results p {
    font-size: 1.1rem;
    max-width: 500px;
    margin: 0 auto;
}
/* إضافة إلى ملف CSS الخاص بك */
.btn.disabled {
    opacity: 0.6;
    cursor: not-allowed !important;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed !important;
}

.fa-spin {
    animation: fa-spin 2s infinite linear;
}

@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 30px;
    gap: 8px;
    flex-wrap: wrap;
    padding: 20px;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    border: 1px solid var(--border-gray);
}

.pagination button {
    min-width: 48px;
    height: 48px;
    border: 2px solid var(--border-gray);
    background: white;
    border-radius: var(--border-radius-sm);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
    font-family: 'Tajawal', sans-serif;
    font-weight: 600;
    color: var(--dark);
    font-size: 1rem;
}

.pagination button:hover:not(:disabled) {
    background-color: var(--primary-light);
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-2px);
}

.pagination button.active {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

.pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.selection-controls {
    display: none;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, var(--primary-light) 0%, #e0e7ff 100%);
    border-radius: var(--border-radius-sm);
    margin-bottom: 25px;
    border: 2px solid var(--primary);
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.selection-info {
    display: flex;
    align-items: center;
    gap: 20px;
}

.selection-info label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    font-weight: 600;
    color: var(--dark);
    font-size: 1.1rem;
}

.selected-count {
    background: var(--primary);
    color: white;
    padding: 10px 24px;
    border-radius: 25px;
    font-weight: bold;
    font-size: 1.1rem;
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    display: flex;
    align-items: center;
    gap: 8px;
}

.toast {
    position: fixed;
    bottom: 30px;
    left: 30px;
    padding: 20px 25px;
    border-radius: var(--border-radius);
    color: white;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: var(--box-shadow-lg);
    z-index: 1000;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.3s, transform 0.3s;
    max-width: 400px;
    backdrop-filter: blur(10px);
}

.toast.show {
    opacity: 1;
    transform: translateY(0);
    animation: toastIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes toastIn {
    0% {
        opacity: 0;
        transform: translateY(20px) scale(0.9);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.toast-success {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.95) 0%, rgba(13, 162, 113, 0.95) 100%);
    border-right: 5px solid var(--success);
}

.toast-error {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.95) 0%, rgba(220, 38, 38, 0.95) 100%);
    border-right: 5px solid var(--danger);
}

.toast-warning {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.95) 0%, rgba(217, 119, 6, 0.95) 100%);
    border-right: 5px solid var(--warning);
}

.toast-info {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.95) 0%, rgba(37, 99, 235, 0.95) 100%);
    border-right: 5px solid var(--info);
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 2000;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
    padding: 20px;
}

.modal-content {
    background: white;
    border-radius: var(--border-radius);
    padding: 40px;
    width: 90%;
    max-width: 500px;
    box-shadow: var(--box-shadow-lg);
    animation: modalSlideIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    border-top: 5px solid var(--primary);
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* ================================
   RESPONSIVE DESIGN - MOBILE FIRST
   ================================ */

/* For tablets and medium screens (992px) */
@media (max-width: 992px) {
    .container {
        max-width: 100%;
        padding: 0 10px;
    }

    .stats-cards {
        grid-template-columns: repeat(2, 1fr);
    }

    .users-table {
        font-size: 0.9rem;
    }

    .users-table th,
    .users-table td {
        padding: 12px 8px;
    }

    /* Show only essential columns on medium screens */
    .users-table th:nth-child(6), /* الفئة */
    .users-table td:nth-child(6),
    .users-table th:nth-child(7), /* الحالة */
    .users-table td:nth-child(7) {
        display: none;
    }

    .action-buttons {
        gap: 5px;
    }

    .btn {
        padding: 6px 10px;
        font-size: 0.8rem;
    }

    .table-container {
        padding: 15px;
    }

    .header {
        padding: 25px 20px;
    }

    .header h1 {
        font-size: 2rem;
    }

    .message-controls {
        grid-template-columns: 1fr;
    }

    .filter-section {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }

    .filter-group {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }

    .filter-section select {
        width: 100%;
        min-width: auto;
    }
}

/* For mobile devices (768px) */
@media (max-width: 768px) {
    body {
        padding: 15px;
    }

    .container {
        padding: 0;
    }

    /* Header adjustments */
    .header {
        padding: 20px 15px;
        margin-bottom: 20px;
    }

    .header h1 {
        font-size: 1.6rem;
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }

    .header p {
        font-size: 1rem;
    }

    /* Stats cards adjustments */
    .stats-cards {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .stat-card {
        padding: 20px;
        flex-direction: column;
        text-align: center;
        min-height: auto;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
    }

    .stat-info h3 {
        font-size: 2rem;
    }

    /* Message section adjustments */
    .message-section {
        padding: 20px;
    }

    .message-controls {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .send-button {
        padding: 15px;
        font-size: 1rem;
    }

    /* Search section adjustments */
    .search-section {
        padding: 20px;
    }

    .search-box {
        flex-direction: column;
        gap: 10px;
    }

    .search-box input {
        width: 100%;
        padding: 15px;
        font-size: 16px; /* Prevents zoom on iOS */
    }

    .filter-section {
        flex-direction: column;
        gap: 10px;
        width: 100%;
    }

    .filter-group {
        width: 100%;
        flex-direction: column;
    }

    .filter-section select {
        width: 100%;
        min-width: auto;
        padding: 12px;
        font-size: 16px; /* Prevents zoom on iOS */
    }

    .search-btn {
        width: 100%;
        padding: 15px;
    }

    /* Table Mobile View - Card Layout */
    .table-container {
        padding: 0;
        margin: 15px 0;
        overflow-x: visible;
    }

    .table-container::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 30px;
        background: linear-gradient(to right, rgba(255,255,255,1), rgba(255,255,255,0));
        pointer-events: none;
        z-index: 5;
    }

    .users-table {
        display: block;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .users-table thead {
        display: none;
    }

    .users-table tbody {
        display: block;
        width: 100%;
    }

    .users-table tr {
        display: block;
        width: 100%;
        margin-bottom: 15px;
        background: white;
        border-radius: var(--border-radius-sm);
        box-shadow: var(--box-shadow);
        padding: 15px;
        border: 1px solid var(--border-gray);
        position: relative;
        overflow: hidden;
    }

    .users-table tr::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        height: 100%;
        background: linear-gradient(to bottom, var(--primary) 0%, var(--secondary) 100%);
    }

    .users-table td {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 12px 0;
        border: none;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.95rem;
        line-height: 1.6;
        width: 100%;
    }

    .users-table td:last-child {
        border-bottom: none;
    }

    .users-table td::before {
        content: attr(data-label);
        font-weight: 700;
        color: var(--primary);
        min-width: 120px;
        padding-left: 10px;
        text-align: left;
        flex-shrink: 0;
        font-size: 0.85rem;
    }

    .users-table td:first-child::before {
        content: 'تحديد';
    }

    .users-table td:last-child::before {
        content: 'الإجراءات';
        align-self: flex-start;
        margin-top: 5px;
    }

    /* Full name styling for mobile */
    .full-name-complete {
        font-size: 1rem;
        font-weight: 600;
        color: var(--dark);
        line-height: 1.5;
        word-break: break-word;
        hyphens: auto;
        text-align: left;
    }

    /* Status badges for mobile */
    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 0.85rem;
        margin-top: 5px;
        width: fit-content;
    }

    /* Action buttons for mobile */
    .action-buttons {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-top: 5px;
        width: 100%;
    }

    .action-buttons .btn {
        width: 100%;
        justify-content: center;
        padding: 10px;
        font-size: 0.85rem;
        min-height: 44px; /* Minimum touch target size */
    }

    /* Hide table header checkbox */
    .users-table thead th:first-child {
        display: none;
    }

    /* Ensure checkboxes are visible */
    .users-table td:first-child {
        display: flex;
        align-items: center;
    }

    .user-checkbox {
        width: 20px;
        height: 20px;
        margin-right: auto;
    }

    /* Pagination adjustments */
    .pagination {
        padding: 15px;
        justify-content: center;
    }

    .pagination button {
        min-width: 40px;
        height: 40px;
        font-size: 0.9rem;
        margin: 2px;
    }

    /* Toast notification */
    .toast {
        left: 10px;
        right: 10px;
        bottom: 10px;
        max-width: none;
    }

    /* Sidebar adjustments */
    .sidebar-menu {
        width: 320px;
        right: -320px;
    }

    .sidebar-menu-btn {
        top: 20px;
        right: 20px;
        padding: 14px 20px;
        font-size: 1rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .sidebar-header {
        padding: 25px 20px;
    }

    .sidebar-content {
        padding: 20px;
        gap: 25px;
    }
}

/* For small mobile devices (576px) */
@media (max-width: 576px) {
    body {
        padding: 10px;
    }

    .container {
        max-width: 100%;
        overflow-x: hidden;
    }

    /* Header adjustments */
    .header h1 {
        font-size: 1.4rem;
        flex-direction: column;
        gap: 10px;
    }

    .stat-info h3 {
        font-size: 1.8rem;
    }

    /* Search box */
    .search-box input {
        padding: 15px;
    }

    /* Further table adjustments */
    .users-table tr {
        padding: 12px;
        margin-bottom: 12px;
    }

    .users-table td {
        padding: 10px 0;
        font-size: 0.9rem;
    }

    .users-table td::before {
        min-width: 100px;
        font-size: 0.85rem;
    }

    .action-buttons {
        grid-template-columns: 1fr;
        gap: 6px;
    }

    .action-buttons .btn {
        padding: 8px;
        font-size: 0.8rem;
        min-height: 36px;
    }

    .full-name-complete {
        font-size: 0.95rem;
    }

    /* Reduce button text size */
    .btn i {
        font-size: 0.7rem;
    }

    /* Hide less important columns on very small screens */
    .users-table td:nth-child(3), /* رقم الهوية */
    .users-table td:nth-child(4), /* الرقم المصري */
    .users-table td:nth-child(5), /* الاستفادة */
    .users-table td:nth-child(6) /* المحافظة */ {
        display: none;
    }

    /* Sidebar adjustments */
    .sidebar-menu {
        width: 100%;
        right: -100%;
    }

    .sidebar-menu-btn .btn-text {
        display: none;
    }

    .sidebar-menu-btn {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        justify-content: center;
        padding: 0;
        top: 15px;
        right: 15px;
    }

    .sidebar-menu-btn i {
        margin: 0;
        font-size: 1.4rem;
    }

    /* Message box */
    .message-box {
        min-height: 120px;
        padding: 15px;
        font-size: 16px; /* Prevents zoom on iOS */
    }
}

/* For very small devices (400px) */
@media (max-width: 400px) {
    .users-table td::before {
        min-width: 80px;
        font-size: 0.8rem;
    }

    .users-table td {
        font-size: 0.85rem;
    }

    .action-buttons {
        grid-template-columns: 1fr;
    }

    .header {
        padding: 15px 10px;
    }

    .message-section,
    .search-section {
        padding: 15px;
    }
}

/* Print Styles */
@media print {
    .search-section,
    .selection-controls,
    .action-buttons,
    .pagination {
        display: none !important;
    }

    .users-table {
        width: 100%;
        table-layout: auto;
    }

    .users-table th,
    .users-table td {
        padding: 8px;
        border: 1px solid #ddd;
    }
}

/* Custom Scrollbars */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: var(--light-gray);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: var(--gray);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--primary);
}

/* Navbar Styles */
.navbar {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    padding: 0.75rem 2rem;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    backdrop-filter: blur(10px);
    margin-bottom: 20px;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.navbar-container {
    max-width: 1800px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    font-size: 1.3rem;
    font-weight: 700;
}

.navbar-brand i {
    font-size: 1.8rem;
    filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.2));
}

.navbar-user {
    display: flex;
    align-items: center;
    gap: 15px;
    background: rgba(255, 255, 255, 0.1);
    padding: 8px 15px 8px 20px;
    border-radius: 50px;
    cursor: pointer;
    transition: var(--transition);
    border: 1px solid rgba(255, 255, 255, 0.1);
    position: relative;
    backdrop-filter: blur(5px);
}

.navbar-user:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.user-info {
    text-align: left;
}

.user-name {
    color: white;
    font-weight: 600;
    font-size: 1rem;
    display: block;
    line-height: 1.4;
}

.user-role {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.8rem;
}

.user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ffd166 0%, #ffb347 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 3px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.user-avatar i {
    font-size: 2.2rem;
    color: white;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.dropdown-icon {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.8rem;
    transition: var(--transition);
}

.navbar-user:hover .dropdown-icon {
    transform: rotate(180deg);
    color: white;
}

/* User Dropdown Menu */
.user-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    left: 0;
    width: 280px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1001;
    overflow: hidden;
}

.user-dropdown.active {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header {
    background: linear-gradient(135deg, var(--primary-light) 0%, #e0e7ff 100%);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.dropdown-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid white;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.dropdown-avatar i {
    font-size: 2.5rem;
    color: white;
}

.dropdown-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.dropdown-user-info {
    flex: 1;
}

.dropdown-user-name {
    display: block;
    font-weight: 700;
    color: var(--dark);
    font-size: 1.1rem;
    margin-bottom: 5px;
}

.dropdown-user-email {
    display: block;
    color: var(--gray);
    font-size: 0.85rem;
}

.dropdown-menu {
    padding: 10px;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 15px;
    border-radius: 12px;
    color: var(--dark);
    text-decoration: none;
    transition: all 0.3s ease;
}

.dropdown-item:hover {
    background: var(--light-gray);
    transform: translateX(-5px);
}

.dropdown-item i {
    width: 20px;
    color: var(--primary);
    font-size: 1.1rem;
}

.dropdown-item.text-danger i,
.dropdown-item.text-danger span {
    color: var(--danger);
}

.dropdown-divider {
    height: 1px;
    background: var(--border-gray);
    margin: 8px 0;
}

/* Responsive Navbar */
@media (max-width: 768px) {
    .navbar {
        padding: 0.5rem 1rem;
    }

    .navbar-brand span {
        display: none;
    }

    .navbar-brand i {
        font-size: 1.5rem;
    }

    .user-info {
        display: none;
    }

    .navbar-user {
        padding: 5px 5px 5px 10px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
    }

    .user-dropdown {
        width: 250px;
        left: auto;
        right: 0;
    }
}

@media (max-width: 576px) {
    .navbar {
        border-radius: var(--border-radius-sm);
    }
    
    .user-dropdown {
        width: 220px;
    }
}
/* تحسينات السايد بار للجوال */
@media (max-width: 768px) {
    .sidebar-menu {
        width: 85%;
        max-width: 350px;
        right: -100%;
        transition: right 0.3s ease-in-out;
        box-shadow: -5px 0 25px rgba(0, 0, 0, 0.3);
    }
    
    .sidebar-menu.active {
        right: 0;
    }
    
    /* زر إغلاق عائم يظهر خارج السايد بار */
    .mobile-close-btn {
        position: fixed;
        top: 15px;
        left: 15px;
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
        color: white;
        border: none;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2001;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4);
        font-size: 1.3rem;
        transition: all 0.3s ease;
    }
    
    .mobile-close-btn.active {
        display: flex;
    }
    
    .mobile-close-btn:hover {
        transform: scale(1.1) rotate(90deg);
        background: linear-gradient(135deg, #dc2626 0%, var(--danger) 100%);
    }
    
    /* تحسين خلفية التعتيم */
    .sidebar-overlay {
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(3px);
        transition: opacity 0.3s ease;
    }
    
    .sidebar-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    /* تحسين زر القائمة الجانبية */
    .sidebar-menu-btn {
        top: 15px;
        right: 15px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        padding: 0;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
        z-index: 1001;
    }
    
    .sidebar-menu-btn .btn-text {
        display: none;
    }
    
    .sidebar-menu-btn i {
        font-size: 1.3rem;
        margin: 0;
    }
    
    /* تحسين محتوى السايد بار للتمرير */
    .sidebar-content {
        padding: 20px 15px;
        -webkit-overflow-scrolling: touch;
        max-height: calc(100vh - 120px);
    }
    
    /* تكبير مناطق اللمس */
    .sidebar-link {
        padding: 15px;
        margin-bottom: 5px;
    }
    
    .sidebar-link-icon {
        width: 45px;
        height: 45px;
        font-size: 1.2rem;
    }
    
    .close-menu-btn {
        width: 40px;
        height: 40px;
    }
    
    /* تحسين الروابط */
    .sidebar-link-title {
        font-size: 1rem;
    }
    
    .sidebar-link-desc {
        font-size: 0.85rem;
    }
    
    /* تحسين بطاقات الإحصائيات */
    .sidebar-stat-card {
        padding: 15px;
    }
    
    .sidebar-stat-card .stat-icon {
        width: 45px;
        height: 45px;
        font-size: 1.3rem;
    }
    
    .sidebar-stat-card .stat-info h4 {
        font-size: 1.3rem;
    }
}

@media (max-width: 576px) {
    .sidebar-menu {
        width: 90%;
    }
    
    .sidebar-header {
        padding: 20px 15px;
    }
    
    .sidebar-header-content i {
        width: 45px;
        height: 45px;
        font-size: 1.5rem;
    }
    
    .sidebar-header h3 {
        font-size: 1.4rem;
    }
    
    .mobile-close-btn {
        top: 10px;
        left: 10px;
        width: 40px;
        height: 40px;
        font-size: 1.1rem;
    }
}
/* ======================================
   تحسينات حاسمة لمشكلة التمرير الأفقي - للجوال فقط
   ====================================== */

/* منع التمرير الأفقي الزائد - للجوال فقط */
@media (max-width: 768px) {
    html, body {
        max-width: 100%;
        overflow-x: hidden;
        position: relative;
    }

    .container {
        max-width: 100%;
        overflow-x: hidden;
        padding: 0 10px;
    }

    /* إصلاح مشكلة عرض الجدول - للجوال فقط */
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 0 -10px;
        padding: 0 10px;
        width: 100vw;
        position: relative;
        left: 50%;
        right: 50%;
        margin-left: -50vw;
        margin-right: -50vw;
    }

    /* إخفاء بعض الأعمدة غير الضرورية على الجوال */
    .users-table th:nth-child(5), /* الاستفادة */
    .users-table td:nth-child(5),
    .users-table th:nth-child(6), /* الكود */
    .users-table td:nth-child(6) {
        display: none;
    }

    /* عرض الأعمدة المتبقية بشكل أفضل */
    .users-table th,
    .users-table td {
        white-space: nowrap;
        font-size: 0.85rem;
        padding: 10px 8px;
    }

    /* تحسين أزرار الإجراءات */
    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .action-buttons .btn {
        padding: 6px 8px;
        font-size: 0.75rem;
    }

    .action-buttons .btn i {
        margin-left: 3px;
    }

    /* تحسين ظهور أزرار التحديد */
    .selection-controls {
        position: sticky;
        top: 60px;
        z-index: 100;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    /* تحسين ظهور زر إنشاء مستخدم جديد */
    .create-user-controls {
        padding: 15px;
        text-align: center;
        background: white;
        border-bottom: 1px solid #eee;
    }

    .create-user-controls .btn {
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
        padding: 15px 20px;
        font-size: 1.1rem;
    }

    /* تحسين ظهور الفلتر */
    .filter-section {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-group {
        width: 100%;
    }

    .filter-group select {
        width: 100%;
        margin: 5px 0;
    }

    .search-box {
        flex-direction: column;
    }

    .search-btn {
        width: 100%;
        margin: 5px 0;
    }

    /* إصلاح مشكلة عرض الـ Sidebar */
    .sidebar-menu {
        width: 85%;
        max-width: 300px;
        z-index: 10000;
    }

    .sidebar-menu-btn {
        position: fixed;
        z-index: 9999;
    }

    .scroll-top-btn {
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        z-index: 9999;
    }
}

@media (max-width: 576px) {
    /* إخفاء المزيد من الأعمدة على الشاشات الصغيرة جداً */
    .users-table th:nth-child(4), /* الرقم المصري */
    .users-table td:nth-child(4),
    .users-table th:nth-child(7), /* الفئة */
    .users-table td:nth-child(7) {
        display: none;
    }

    /* عرض باقي الأعمدة بشكل مناسب */
    .users-table th:nth-child(2), /* الاسم */
    .users-table td:nth-child(2) {
        min-width: 120px;
    }

    .users-table th:nth-child(3), /* رقم الهوية */
    .users-table td:nth-child(3) {
        min-width: 90px;
    }

    /* تحسين عرض الاسم */
    .full-name-complete {
        font-size: 0.9rem;
        white-space: normal;
        word-wrap: break-word;
        max-width: 120px;
    }

    /* تقليص أزرار الإجراءات أكثر */
    .action-buttons {
        flex-direction: column;
    }

    .action-buttons .btn {
        width: 100%;
        padding: 8px 5px;
    }
}

/* ======================================
   تحسين ظهور رسائل النجاح/الفشل - للجوال فقط
   ====================================== */

@media (max-width: 768px) {
    /* جعل رسالة الإشعار تظهر في أعلى الصفحة */
    .toast {
        position: fixed;
        top: 20px !important;
        left: 50% !important;
        right: auto !important;
        bottom: auto !important;
        transform: translateX(-50%) translateY(-100px);
        z-index: 9999;
        min-width: 300px;
        max-width: 90%;
        text-align: center;
        border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

    .toast.show {
        transform: translateX(-50%) translateY(0);
    }

    /* تحسين ظهور المودال (النافذة المنبثقة) */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 99999;
        align-items: flex-start !important;
        justify-content: center;
        padding: 20px 15px;
        overflow-y: auto;
    }

    .modal-content {
        background: white;
        border-radius: 16px;
        padding: 30px 20px;
        width: 100%;
        max-width: 400px;
        margin: 20px auto;
        position: relative;
        top: 10%;
        animation: modalSlideDown 0.3s ease-out;
    }

    @keyframes modalSlideDown {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
}

/* ======================================
   إصلاح مشكلة عرض العناصر على الكمبيوتر
   ====================================== */

/* إعادة ضبط العناصر للكمبيوتر */
@media (min-width: 769px) {
    .table-container {
        overflow-x: auto;
        width: 100%;
        margin: 0;
        left: auto;
        right: auto;
    }

    .create-user-controls {
        padding: 15px 25px;
        text-align: left;
    }

    .create-user-controls .btn {
        width: auto;
        display: inline-flex;
    }

    .toast {
        position: fixed;
        bottom: 30px;
        left: 30px;
        top: auto;
        right: auto;
        transform: translateY(20px);
    }

    .toast.show {
        transform: translateY(0);
    }

    .modal {
        align-items: center !important;
    }

    .modal-content {
        top: 0;
    }
}
.benefit-danger {
    color: red;
    font-weight: bold;
}

.benefit-wrapper {
    position: relative;
    display: inline-block;
}

.benefit-number {
    font-size: 16px;
    font-weight: bold;
}

.benefit-plus {
    position: absolute;
    top: -6px;
    right: -8px;
    font-size: 12px;
    color: #999;
    font-weight: bold;
}
.benefit-plus {
    color: red;
}


.row-success:hover {
    background: linear-gradient(135deg, #d3f9d8, #b2f2bb);
}

/* 🔴 صف فيه ملاحظات */
.row-danger {
    background: linear-gradient(135deg, #fff5f5, #ffe3e3);
    border-right: 4px solid #ff4d4f;
    transition: all 0.3s ease;
}

.row-danger:hover {
    background: linear-gradient(135deg, #ffe3e3, #ffc9c9);
}

/* 🟢 صف معتمد */
.row-success {
    background: linear-gradient(135deg, #f0fff4, #d3f9d8);
    border-right: 4px solid #2f9e44;
    transition: all 0.3s ease;
}


/* ✨ تحسين عام */
.users-table tr {
    transition: all 0.2s ease-in-out;
}
@media (max-width: 768px) {

    .row-danger {
        background: linear-gradient(135deg, #fff5f5, #ffe3e3) !important;
        border-right: 4px solid #ff4d4f;
    }

    .row-success {
        background: linear-gradient(135deg, #f0fff4, #d3f9d8) !important;
        border-right: 4px solid #2f9e44;
    }
}


/* Main Statistics Section */
    .stats-main-section {
        padding: 0 15px;
        margin-bottom: 25px;
    }

    .stats-grid-custom {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 15px;
    }

    /* Individual Stat Card */
    .stat-card-custom {
        background: #ffffff;
        border-radius: 16px;
        padding: 18px 20px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.04);
        cursor: pointer;
    }

    .stat-card-custom:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    /* Colored top border for each stat */
    .stat-card-custom::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        border-radius: 16px 16px 0 0;
    }

    .stat-total::before { background: linear-gradient(90deg, #4a6cf7, #6c8cff); }
    .stat-orphans::before { background: linear-gradient(90deg, #f59f00, #ffd43b); }
    .stat-amputations::before { background: linear-gradient(90deg, #e03131, #ff6b6b); }
    .stat-injured::before { background: linear-gradient(90deg, #2f9e44, #51cf66); }
    .stat-patients::before { background: linear-gradient(90deg, #7048e8, #9775fa); }
    .stat-stranded::before { background: linear-gradient(90deg, #fd7e14, #ffa94d); }

    /* Card Inner Content */
    .stat-card-inner {
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
        z-index: 1;
    }

    /* Icon Wrapper */
    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .stat-total .stat-icon-wrapper {
        background: rgba(74, 108, 247, 0.1);
        color: #4a6cf7;
    }

    .stat-orphans .stat-icon-wrapper {
        background: rgba(245, 159, 0, 0.1);
        color: #f59f00;
    }

    .stat-amputations .stat-icon-wrapper {
        background: rgba(224, 49, 49, 0.1);
        color: #e03131;
    }

    .stat-injured .stat-icon-wrapper {
        background: rgba(47, 158, 68, 0.1);
        color: #2f9e44;
    }

    .stat-patients .stat-icon-wrapper {
        background: rgba(112, 72, 232, 0.1);
        color: #7048e8;
    }

    .stat-stranded .stat-icon-wrapper {
        background: rgba(253, 126, 20, 0.1);
        color: #fd7e14;
    }

    .stat-card-custom:hover .stat-icon-wrapper {
        transform: scale(1.05) rotate(-5deg);
    }

    /* Info Wrapper */
    .stat-info-wrapper {
        flex: 1;
        min-width: 0;
    }

    .stat-number {
        font-size: 26px;
        font-weight: 700;
        color: #212529;
        line-height: 1.2;
        margin-bottom: 2px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .stat-label {
        font-size: 13px;
        color: #6c757d;
        font-weight: 500;
        white-space: nowrap;
    }

    /* Badge */
    .stat-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(47, 158, 68, 0.08);
        color: #2f9e44;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .stat-badge i {
        font-size: 10px;
    }

    .stat-badge:has(.fa-arrow-down) {
        background: rgba(224, 49, 49, 0.08);
        color: #e03131;
    }

    .stat-card-custom:hover .stat-badge {
        transform: translateX(3px);
    }

    /* Progress Bar */
    .stat-progress-bar {
        margin-top: 12px;
        height: 3px;
        background: #f1f3f5;
        border-radius: 4px;
        overflow: hidden;
        position: relative;
        z-index: 1;
    }

    .stat-progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 1.5s ease;
        width: 0;
    }

    .stat-total .stat-progress-fill { background: linear-gradient(90deg, #4a6cf7, #6c8cff); }
    .stat-orphans .stat-progress-fill { background: linear-gradient(90deg, #f59f00, #ffd43b); }
    .stat-amputations .stat-progress-fill { background: linear-gradient(90deg, #e03131, #ff6b6b); }
    .stat-injured .stat-progress-fill { background: linear-gradient(90deg, #2f9e44, #51cf66); }
    .stat-patients .stat-progress-fill { background: linear-gradient(90deg, #7048e8, #9775fa); }
    .stat-stranded .stat-progress-fill { background: linear-gradient(90deg, #fd7e14, #ffa94d); }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card-custom {
        animation: fadeInUp 0.6s ease forwards;
        opacity: 0;
    }

    .stat-card-custom:nth-child(1) { animation-delay: 0.05s; }
    .stat-card-custom:nth-child(2) { animation-delay: 0.1s; }
    .stat-card-custom:nth-child(3) { animation-delay: 0.15s; }
    .stat-card-custom:nth-child(4) { animation-delay: 0.2s; }
    .stat-card-custom:nth-child(5) { animation-delay: 0.25s; }
    .stat-card-custom:nth-child(6) { animation-delay: 0.3s; }

    @keyframes countUp {
        from {
            opacity: 0;
            transform: translateY(5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-number {
        animation: countUp 0.8s ease forwards;
    }

    /* Responsive Design */
    @media (max-width: 1400px) {
        .stats-grid-custom {
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
    }

    @media (max-width: 992px) {
        .stats-grid-custom {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .stat-number {
            font-size: 22px;
        }

        .stat-icon-wrapper {
            width: 42px;
            height: 42px;
            font-size: 18px;
        }
    }

    @media (max-width: 576px) {
        .stats-grid-custom {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .stat-card-custom {
            padding: 15px;
        }

        .stat-number {
            font-size: 20px;
        }

        .stat-label {
            font-size: 12px;
        }

        .stat-badge {
            font-size: 10px;
            padding: 2px 8px;
        }
    }


.navbar-container{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.navbar-actions{
    display:flex;
    align-items:center;
    gap:20px;
}

.notification-btn{
    position:relative;
    color:#555;
    font-size:22px;
    text-decoration:none;
    transition:.3s;
}

.notification-btn:hover{
    color:#0d6efd;
}

.notification-badge{
    position:absolute;
    top:-8px;
    right:-8px;
    min-width:18px;
    height:18px;
    border-radius:50%;
    background:#dc3545;
    color:#fff;
    font-size:11px;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:2px 5px;
}

.stat-details {
    margin-top: 15px;
    padding: 12px 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
}

.stat-detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 7px 0;
    font-size: 13px;
}

.stat-detail-row span {
    color: rgba(255, 255, 255, 0.75);
}

.stat-detail-row strong {
    color: #fff;
    font-size: 14px;
    white-space: nowrap;
}

.stat-card-custom .stat-detail-row strong {
    color: #212529;
}

.stat-card-custom .stat-detail-row span {
    color: #6c757d;
}

.delete-btn {
    background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);
    color: white;
    transition: all 0.3s ease;
}

.delete-btn:hover {
    background: linear-gradient(135deg, #a71d2a 0%, #dc3545 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
}

.delete-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <i class="fab fa-telegram"></i>
                <span>لوحة إدارة التيليجرام</span>
            </div>
             <div class="navbar-actions">
            <!-- Notifications -->
            @if(Auth::check() && Auth::user()->hasRole('superadmin'))
                <a href="{{ route('reminders.index') }}" class="notification-btn">
                    <i class="fas fa-bell"></i>

                    @if($count)
                        <span class="notification-badge">
                            {{ $count }}
                        </span>
                    @endif
                </a>
            @endif
            </div>
            <div class="navbar-user" onclick="toggleUserMenu()">
                <div class="user-info">
                    <span class="user-name" id="navbarUserName">
                        @if(Auth::check())
                            {{ implode(' ', array_slice(explode(' ', Auth::user()->name), 0, 2)) }}
                        @else
                            مسؤول النظام
                        @endif
                    </span>
                    <span class="user-role">
                        @if(Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('admin'))
                            مدير النظام
                        @else
                            أدمن
                        @endif
                    </span>
                </div>
                <div class="user-avatar" id="navbarUserAvatar">
                    @if(Auth::user() && Auth::user()->photo)
                        <img src="{{ Auth::user()->photo }}" alt="User Avatar">
                    @else
                        <i class="fas fa-user-circle"></i>
                    @endif
                </div>
                <i class="fas fa-chevron-down dropdown-icon"></i>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-avatar">
                            @if(Auth::user() && Auth::user()->photo)
                                <img src="{{ Auth::user()->photo }}" alt="User Avatar">
                            @else
                                <i class="fas fa-user-circle"></i>
                            @endif
                        </div>
                        <div class="dropdown-user-info">
                            <span class="dropdown-user-name">{{ Auth::user()->name ?? 'مسؤول النظام' }}</span>
                            <span class="dropdown-user-email">{{ Auth::user()->email ?? 'admin@example.com' }}</span>
                        </div>
                    </div>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>الملف الشخصي</span>
                        </a>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            <span>الإعدادات</span>
                        </a>
                        <div class="dropdown-divider"></div>
                            <a href="{{ route('logout') }}" 
                            class="dropdown-item text-danger"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>تسجيل الخروج</span>
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                    </div>
                </div>
                
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Sidebar Menu -->
        <!-- Sidebar Menu Button -->
        <button class="sidebar-menu-btn" onclick="toggleSidebarMenu()">
            <i class="fas fa-bars"></i>
            <span class="btn-text">القائمة الجانبية</span>
        </button>

        <!-- Sidebar Menu -->
        <div class="sidebar-menu" id="sidebarMenu">
            <div class="sidebar-header">
                <a href="{{ route('admin.index') }}" class="text-decoration-none">
                    <div class="sidebar-header-content">
                        <i class="fas fa-users"></i>
                        <h3>لوحة التحكم الرئيسية</h3>
                    </div>
                </a>
                <button class="close-menu-btn" onclick="toggleSidebarMenu()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="sidebar-content">
                <div class="sidebar-section">
                    <h4 class="sidebar-section-title">
                        <i class="fas fa-link"></i> الروابط السريعة
                    </h4>
                    <div class="sidebar-links">
                        <a href="{{ route('events.index') }}" class="sidebar-link">
                            <div class="sidebar-link-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="sidebar-link-content">
                                <span class="sidebar-link-title">الفعاليات</span>
                                <span class="sidebar-link-desc">إدارة وانشاء الفعاليات</span>
                            </div>
                        </a>

                        <a href="{{ route('reports.event-distribution') }}" class="sidebar-link">
                            <div class="sidebar-link-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="sidebar-link-content">
                                <span class="sidebar-link-title">تقارير الفعاليات</span>
                            </div>
                        </a>
                        
                        <a href="{{ route('events.fixed') }}" class="sidebar-link">
                            <div class="sidebar-link-icon">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <div class="sidebar-link-content">
                                <span class="sidebar-link-title">الفعاليات الثابتة</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.categories.index') }}" class="sidebar-link">
                            <div class="sidebar-link-icon">
                                <i class="fas fa-th-large"></i>
                            </div>
                            <div class="sidebar-link-content">
                                <span class="sidebar-link-title">الفئات</span>
                                <span class="sidebar-link-desc">إدارة وانشاء الفئات</span>
                            </div>
                        </a>
                        
                        <a href="{{ route('search.advance') }}" class="sidebar-link">
                            <div class="sidebar-link-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="sidebar-link-content">
                                <span class="sidebar-link-title">بحث متقدم</span>
                            </div>
                        </a>
                        
                        <a href="{{ route('admin.messages.index') }}" class="sidebar-link">
                            <div class="sidebar-link-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="sidebar-link-content">
                                <span class="sidebar-link-title">الرسائل</span>
                                <span class="sidebar-link-desc">إدارة جميع الرسائل</span>
                            </div>
                        </a>

                        <a href="{{ route('wheelchair.index') }}" class="sidebar-link">
                            <div class="sidebar-link-icon">
                                <i class="fas fa-wheelchair"></i>
                            </div>
                            <div class="sidebar-link-content">
                                <span class="sidebar-link-title">طلبات الكراسي المتحركة</span>
                                <span class="sidebar-link-desc">إدارة ومراجعة طلبات الكراسي المتحركة</span>
                            </div>
                        </a> 

                        <a href="{{ route('photographer.applications') }}" class="sidebar-link">
                            <div class="sidebar-link-icon">
                                <i class="fas fa-camera"></i>
                            </div>
                            <div class="sidebar-link-content">
                                <span class="sidebar-link-title">طلبات المصورين</span>
                                <span class="sidebar-link-desc">إدارة ومراجعة طلبات المصورين</span>
                            </div>
                        </a>
                        
                        <a href="{{ route('ramadan.index') }}" class="sidebar-link">
                            <div class="sidebar-link-icon">
                                <i class="fas fa-moon"></i> 
                            </div>
                            <div class="sidebar-link-content">
                                <span class="sidebar-link-title">شهر رمضان</span>
                                <span class="sidebar-link-desc">نظام إدارة المستفيدين وتوزيع الوجبات اليومية</span>
                            </div>
                        </a>
                        <a href="{{ route('embassy.index') }}" class="sidebar-link">
                            <div class="sidebar-link-icon">
                                <i class="fas fa-building"></i> 
                            </div>
                            <div class="sidebar-link-content">
                                <span class="sidebar-link-title">فعالية السفارة</span>
                                <span class="sidebar-link-desc">نظام إدارة حضور فعالية السفارة</span>
                            </div>
                        </a>
                        <a href="{{ route('admin.telegram.index') }}" class="sidebar-link">
                            <div class="sidebar-link-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="sidebar-link-content">
                                <span class="sidebar-link-title">المستخدمين</span>
                                <span class="sidebar-link-desc">إدارة جميع المستخدمين</span>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="sidebar-stats">
                    <h4 class="sidebar-section-title">
                        <i class="fas fa-chart-bar"></i> الإحصائيات
                    </h4>
                    <div class="stats-grid">
                        <div class="sidebar-stat-card">
                            <div class="stat-icon total">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h4 id="sidebarTotalUsers">{{ $totalUsers }}</h4>
                                <p>إجمالي المستخدمين</p>
                            </div>
                        </div>

                        <div class="sidebar-stat-card">
                            <div class="stat-icon completed">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h4 id="sidebarCompletedUsers">{{ $is_completed }}</h4>
                                <p>المستخدمين المكتملين</p>
                            </div>
                        </div>

                        <div class="sidebar-stat-card">
                            <div class="stat-icon blocked">
                                <i class="fas fa-ban"></i>
                            </div>
                            <div class="stat-info">
                                <h4 id="sidebarBlockedUsers">{{ $is_blocked }}</h4>
                                <p>المستخدمين قيد التسجيل مرحلة اولى</p>
                            </div>
                        </div>

                        <div class="sidebar-stat-card">
                            <div class="stat-icon pending">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h4 id="sidebarPendingUsers">{{ $pendingUsers }}</h4>
                                <p>قيد التسجيل</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="sidebar-footer-content">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <p class="footer-user">مسؤول النظام</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebarMenu()"></div>
        <br>

        <!-- Statistics Cards Section -->
<div class="stats-main-section">
    <div class="stats-grid-custom">
        <!-- Total Registered -->
        <div class="stat-card-custom stat-total">
            <div class="stat-card-inner">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info-wrapper">
                    <div class="stat-number" id="totalRegistered">0</div>
                    <div class="stat-label">إجمالي المسجلين</div>
                </div>
                <div class="stat-badge">
                    <i class="fas fa-arrow-up"></i>
                    <span>+12%</span>
                </div>
            </div>
            <div class="stat-progress-bar">
                <div class="stat-progress-fill" style="width: 100%"></div>
            </div>
        </div>

        <!-- Orphans -->
        <!-- 👶 Orphans -->
<div class="stat-card-custom stat-orphans">
    <div class="stat-card-inner">
        <div class="stat-icon-wrapper">
            <i class="fas fa-child"></i>
        </div>

        <div class="stat-info-wrapper">
            <div class="stat-number">
                {{ number_format($orphansCount) }}
            </div>

            <div class="stat-label">إجمالي الأيتام</div>
        </div>
    </div>

    <div class="stat-details">

        <div class="stat-detail-row">
            <span>المسجلون عبر الرابط</span>
            <strong>{{ number_format($orphansRegisteredInLink) }}</strong>
        </div>

        <div class="stat-detail-row">
            <span>المتبقي غير المسجل</span>
            <strong>{{ number_format($orphansRemaining) }}</strong>
        </div>

        <div class="stat-detail-row">
            <span>الحاصلون على كود</span>
            <strong>{{ number_format($orphansWithCode) }}</strong>
        </div>

        <div class="stat-detail-row">
            <span>المتبقي بدون كود</span>
            <strong>{{ number_format($orphansWithoutCode) }}</strong>
        </div>

    </div>

    <div class="stat-progress-bar">
        <div class="stat-progress-fill"
             style="width: {{ $orphansCount > 0 ? min(100, ($orphansRegisteredInLink / $orphansCount) * 100) : 0 }}%">
        </div>
    </div>
</div>

        <!-- Amputations -->
        <!-- 🦽 Amputations -->
<div class="stat-card-custom stat-amputations">
    <div class="stat-card-inner">
        <div class="stat-icon-wrapper">
            <i class="fas fa-wheelchair"></i>
        </div>

        <div class="stat-info-wrapper">
            <div class="stat-number">
                {{ number_format($amputationsCount) }}
            </div>

            <div class="stat-label">إجمالي حالات البتر</div>
        </div>
    </div>

    <div class="stat-details">

        <div class="stat-detail-row">
            <span>المسجلون عبر الرابط</span>
            <strong>{{ number_format($amputationsRegisteredInLink) }}</strong>
        </div>

        <div class="stat-detail-row">
            <span>المتبقي غير المسجل</span>
            <strong>{{ number_format($amputationsRemaining) }}</strong>
        </div>

        <div class="stat-detail-row">
            <span>الحاصلون على كود</span>
            <strong>{{ number_format($amputationsWithCode) }}</strong>
        </div>

        <div class="stat-detail-row">
            <span>المتبقي بدون كود</span>
            <strong>{{ number_format($amputationsWithoutCode) }}</strong>
        </div>

    </div>

    <div class="stat-progress-bar">
        <div class="stat-progress-fill"
             style="width: {{ $amputationsCount > 0 ? min(100, ($amputationsRegisteredInLink / $amputationsCount) * 100) : 0 }}%">
        </div>
    </div>
</div>

        <!-- Injured -->
        <div class="stat-card-custom stat-injured">
            <div class="stat-card-inner">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-hospital-user"></i>
                </div>
                <div class="stat-info-wrapper">
                    <div class="stat-number" id="injuredCount">0</div>
                    <div class="stat-label">حالات الجرحى</div>
                </div>
                <div class="stat-badge">
                    <i class="fas fa-arrow-up"></i>
                    <span>+7%</span>
                </div>
            </div>
            <div class="stat-progress-bar">
                <div class="stat-progress-fill" style="width: 27%"></div>
            </div>
        </div>

        <!-- Patients -->
        <div class="stat-card-custom stat-patients">
            <div class="stat-card-inner">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="stat-info-wrapper">
                    <div class="stat-number" id="patientsCount">0</div>
                    <div class="stat-label">حالات المرضى</div>
                </div>
                <div class="stat-badge">
                    <i class="fas fa-arrow-up"></i>
                    <span>+15%</span>
                </div>
            </div>
            <div class="stat-progress-bar">
                <div class="stat-progress-fill" style="width: 35%"></div>
            </div>
        </div>

        <!-- Stranded -->
        <div class="stat-card-custom stat-stranded">
            <div class="stat-card-inner">
                <div class="stat-icon-wrapper">
                    <i class="fas fa-route"></i>
                </div>
                <div class="stat-info-wrapper">
                    <div class="stat-number" id="strandedCount">0</div>
                    <div class="stat-label">حالات العالقين</div>
                </div>
                <div class="stat-badge">
                    <i class="fas fa-route"></i>
                    <span>30%</span>
                </div>
            </div>
            <div class="stat-progress-bar">
                <div class="stat-progress-fill" style="width: 50%"></div>
            </div>
        </div>
    </div>
</div>

        <br>

        <!-- Search and Table Container -->
        <div class="search-table-container">

            <div class="search-section">
                <form method="GET" action="{{ route('admin.telegram.index') }}">
                    <div class="search-box">

                        <input
                            type="text"
                            name="search"
                            value=""
                            placeholder="{{ request('search') ? '🔍 نتيجة آخر بحث: \"' . request('search') . '\" — اكتب بحث جديد' : '🔍 ابحث بالاسم، الهوية، الأرقام، الكود...' }}"
                        >
                        <div class="filter-section">
                            <div class="filter-group">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="">📊 جميع الحالات</option>
                                    <option value="completed" {{ request('status')=='completed'?'selected':'' }}>✅ مكتمل</option>
                                    <option value="blocked" {{ request('status')=='blocked'?'selected':'' }}>⛔ فشل التسجيل</option>
                                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>🟡 قيد التسجيل</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="event_id" value="{{ $eventId }}">
                        <input type="hidden" name="message_type" value="{{ $messageType }}">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                            بحث
                        </button>
                    </div>
                </form>
            </div>
            <div class="create-user-controls">
                <a href="{{ route('admin.telegram.create') }}"
                class="btn btn-primary btn-lg shadow-sm px-4 rounded-pill d-inline-flex align-items-center gap-2 transition-all">
                    <i class="fas fa-plus-circle"></i>
                    <span>إنشاء مستخدم جديد</span>
                </a>
            </div>
        </div>
        @if(request()->filled('search'))
            <div style="margin: 10px 0; padding: 10px 15px; background: var(--primary-light); border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                <span>
                    <i class="fas fa-filter"></i>
                    نتائج البحث عن: <strong>"{{ request('search') }}"</strong>
                </span>
            </div>
        @endif
        @if($users->isEmpty())
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>لا توجد نتائج للبحث</h3>
                <p>
                    @if(request()->filled('search') || request()->filled('status'))
                        لم يتم العثور على مستخدمين يتطابقون مع معايير البحث الحالية.
                        <br>
                        <a href="{{ route('admin.telegram.index') }}" style="color: var(--primary); font-weight: 600; text-decoration: underline;">
                            عرض جميع المستخدمين
                        </a>
                    @else
                        لا يوجد أي مستخدمين مسجلين حتى الآن.
                    @endif
                </p>
            </div>
        @else
            <!-- Table Container -->
            <div class="table-container">
                <table class="users-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="tableSelectAll" onchange="toggleTableSelectAll()">
                            </th>
                            <th>
                                <i class="fas fa-user"></i> الاسم الكامل
                            </th>
                            <th>
                                <i class="fas fa-id-card"></i> رقم الهوية
                            </th>
                            <th>
                                <i class="fas fa-phone"></i> رقم مصر
                            </th>

                            <th>
                                <i class="fas fa-map-marker-alt"></i>المدينة
                            </th>
                            <th>
                                <i class="fas fa-tag"></i> الفئة
                            </th>

                            <th>
                                <i class="fas fa-info-circle"></i> الحالة
                            </th>
                            <th>
                                <i class="fas fa-cogs"></i> الإجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        @foreach($users as $index => $user)

                            @php
                                $hasNotes = ($user->notes_count ?? 0) > 0;
                                $isApproved = $user->is_approved;
                            @endphp

                            <tr class="{{ $hasNotes ? 'row-danger' : ($isApproved ? 'row-success' : '') }}"
                                data-id="{{ $user->id }}"
                                data-status="{{ $user->is_blocked ? 'blocked' : ($user->is_completed ? 'completed' : 'pending') }}"
                                data-search="{{ strtolower(
                                    ($user->full_name ?? $user->first_name).' '.
                                    $user->national_id.' '.
                                    $user->egypt_number.' '.
                                    $user->telegram_number.' '.
                                    $user->family_members.' '.
                                    $user->city.' '.
                                    $user->target_case.' '.
                                    ($user->governorate ?? '').' '.
                                    ($user->city ?? '').' '.
                                    ($user->address ?? '').' '.
                                    ($user->blocked_reason ?? '')
                                ) }}">

                            <td data-label="تحديد">
                                <input type="checkbox" class="user-checkbox" value="{{ $user->id }}" onchange="updateSelectedCount()">
                            </td>
                            <td data-label="الاسم الكامل">
                                <div class="full-name-complete">

                                    {{-- 🔴 ملاحظات --}}
                                    @if($user->notes_count > 0)
                                        <span style="color:#ff4d4f; margin-left:6px;">⚠️</span>
                                    @endif

                                    {{-- 🟢 معتمد --}}
                                    @if($user->is_approved && $user->notes_count == 0)
                                        <span style="color:#2f9e44; margin-left:6px;">✅</span>
                                    @endif

                                    {{ $user->full_name ?? $user->first_name ?? 'غير متوفر' }}
                                </div>
                            </td>
                            <td data-label="رقم الهوية">{{ $user->national_id ?? 'غير متوفر' }}</td>
                            <td data-label="الرقم المصري">{{ $user->egypt_number ?? 'غير متوفر' }}</td>
                            <td data-label="المدينة">{{ $user->city ?? 'غير متوفر' }}</td>
                            <td data-label="الفئة">{{ $user->target_case ?? 'غير متوفر' }}</td>
                            <td data-label="الحالة">
                                @if($user->is_blocked)
                                    <span class="status-badge status-blocked">
                                        <i class="fas fa-ban"></i> فشل التسجيل
                                    </span>
                                @elseif($user->is_completed)
                                    <span class="status-badge status-completed">
                                        <i class="fas fa-check-circle"></i> مكتمل
                                    </span>
                                @else
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock"></i> قيد التسجيل
                                    </span>
                                @endif
                            </td>
                            <td data-label="الإجراءات">
                                <div class="action-buttons">
                                    <button class="btn btn-success btn-sm"
                                        onclick="sendTelegramMessage(
                                            {{ $user->id }},
                                            '{{ $user->full_name ?? $user->first_name ?? 'المستخدم' }}',
                                            '{{ $user->telegram_number ?? '' }}'
                                        )">

                                        <i class="fas fa-paper-plane"></i> إرسال
                                    </button>

                                    <a href="{{ route('admin.telegram.details', ['id' => $user->id, 'locale' => app()->getLocale()]) }}"
                                        class="btn btn-primary btn-sm interactive-hover"
                                        target="_blank">
                                            <i class="fas fa-eye"></i> عرض
                                    </a>

                                    <a href="{{ route('admin.telegram.edit', ['id' => $user->id, 'locale' => app()->getLocale()]) }}"
                                    class="btn btn-warning btn-sm text-white shadow-sm"
                                        target="_blank">
                                        <i class="fas fa-edit"></i> تعديل
                                    </a>
                                    
                                    <a href="{{ route('admin.telegram.generate-qr', ['id' => $user->id]) }}" class="btn btn-primary">
                                        إنشاء QR
                                    </a>
                                    @if(!$user->is_approved)
                                    <a href="{{ route('admin.telegram.approve.form', $user->id) }}"
                                    class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i> اعتماد
                                    </a>
                                    @endif
                                    @if($user->username)
                                    <a href="https://t.me/{{ $user->username }}" target="_blank" class="btn btn-telegram">
                                        <i class="fab fa-telegram"></i> محادثة
                                    </a>
                                    @endif
                                    @if($user->is_blocked)
                                        <button onclick="unblockUser({{ $user->id }})" 
                                                class="btn btn-warning btn-sm unblock-btn"
                                                data-user-id="{{ $user->id }}">
                                            <i class="fas fa-unlock"></i> اعادة التسجيل
                                        </button>
                                    @endif
                                    {{-- 🔴 زر الحذف - للسوبر أدمن فقط --}}
                                    @if(Auth::user()->hasRole('superadmin'))
                                        <button onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->full_name ?? $user->first_name ?? 'المستخدم') }}')" 
                                                class="btn btn-danger btn-sm delete-btn"
                                                data-user-id="{{ $user->id }}">
                                            <i class="fas fa-trash-alt"></i> حذف
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Pagination -->
        <div class="pagination" id="pagination">
            {{ $users->withQueryString()->links() }}
        </div>
        <!-- Selection Controls -->
        <div class="selection-controls" id="selectionControls">
            <div class="selection-info">
                <label>
                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                    تحديد الكل
                </label>
                <div class="selected-count" id="selectedCount">
                    <i class="fas fa-check-circle"></i>
                    <span>0</span> مستخدم محدد
                </div>
            </div>
        </div>

        <!-- Message Section -->
        <div class="message-section">
            <div class="message-header">
                <i class="fas fa-envelope"></i>
                <h2>نص الرسالة المرسلة للمستخدمين</h2>
            </div>

            <!-- Message Controls -->
            <div class="message-controls">
                <div class="control-group">
                    <label><i class="fas fa-calendar-alt"></i> اختر الفاعلية:</label>

                    @if($eventId && $selectedEvent)
                        {{-- 🔒 جاي من فعالية --}}
                        <select id="eventSelect" disabled>
                            <option value="{{ $selectedEvent->id }}" selected>
                                {{ $selectedEvent->name }} - {{ $selectedEvent->event_date }}
                            </option>
                        </select>

                        {{-- 🔥 مهم عشان القيمة تنرسل --}}
                        <input type="hidden" id="eventIdHidden" value="{{ $selectedEvent->id }}">

                    @else
                        {{-- 🟢 الوضع الطبيعي --}}
                        <select id="eventSelect">
                            <option value="">-- بدون فاعلية --</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}"
                                    {{ $eventId == $event->id ? 'selected' : '' }}>
                                    {{ $event->name }} - {{ $event->event_date }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                </div>

                <div class="control-group">
                    <label><i class="fas fa-envelope-open-text"></i> نوع الرسالة:</label>

                    @if($eventId && $messageType)
                        {{-- 🔒 جاي من فعالية → عرض فقط --}}
                        <select id="messageTypeSelect" disabled>
                            <option value="{{ $messageType }}" selected>
                                @switch($messageType)
                                    @case('received') ✅ تم الاستلام @break
                                    @case('invitation') 📩 دعوة حضور @break
                                    @case('attended') 📝 تم الحضور @break
                                    @case('general') 📢 عامة @break
                                @endswitch
                            </option>
                        </select>

                        {{-- 🔥 مهم جدًا: عشان القيمة تنرسل --}}
                        <input type="hidden" id="messageTypeHidden" value="{{ $messageType }}">
                    
                    @else
                        {{-- 🟢 الوضع الطبيعي --}}
                        <select id="messageTypeSelect">
                            <option value="received" {{ $messageType == 'received' ? 'selected' : '' }}>✅ تم الاستلام</option>
                            <option value="invitation" {{ $messageType == 'invitation' ? 'selected' : '' }}>📩 دعوة حضور</option>
                            <option value="attended" {{ $messageType == 'attended' ? 'selected' : '' }}>📝 تم الحضور</option>
                            <option value="general" {{ $messageType == 'general' ? 'selected' : '' }}>📢 عامة</option>
                        </select>
                    @endif

                </div>

@if($selectedEvent && (
    $selectedEvent->is_archived ||
    $selectedEvent->without_notification
))
    <select id="sendMethodSelect" disabled>
        <option value="without_notification" selected>
            بدون إشعار (توثيق فقط 🔕)
        </option>
    </select>

    <input type="hidden"
           id="sendMethodHidden"
           value="without_notification">

@else

    <select id="sendMethodSelect">
        <option value="with_notification"
            {{ ($sendMethod ?? '') == 'with_notification' ? 'selected' : '' }}>
            بإشعار (إرسال تيليجرام 🔔)
        </option>

        <option value="without_notification"
            {{ ($sendMethod ?? '') == 'without_notification' ? 'selected' : '' }}>
            بدون إشعار (توثيق فقط 🔕)
        </option>
    </select>

@endif
            </div>

            <!-- Message Box -->
            <div class="message-box-container">
                <textarea id="adminMessage"
          class="message-box"
          rows="4"
          placeholder="اكتب الرسالة التي تريد إرسالها إلى المستخدمين...">{{ $autoMessage }}</textarea>
            </div>

            <!-- Send Button -->
            <button class="send-button" onclick="sendToAllSelected()">
                <i class="fas fa-paper-plane"></i> إرسال الرسالة للمستخدمين المحددين
            </button>
        </div>

        
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">تم تنفيذ العملية بنجاح</span>
    </div>

    <!-- Send Confirmation Modal -->
    <div id="sendConfirmModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border-radius: var(--border-radius) var(--border-radius) 0 0; padding: 20px; display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-paper-plane" style="font-size: 1.5rem;"></i>
                <h3 style="margin: 0;">تأكيد إرسال الرسالة</h3>
            </div>
            <div class="modal-body" id="sendConfirmModalBody" style="padding: 30px; font-size: 1.1rem; line-height: 1.6;">
                <!-- سيتم تعبئته بالجافاسكريبت -->
            </div>
            <div class="modal-footer" style="padding: 20px; background: var(--light-gray); display: flex; gap: 15px; justify-content: flex-end; border-radius: 0 0 var(--border-radius) var(--border-radius);">
                <button class="btn btn-primary" onclick="closeSendConfirmModal()" style="padding: 12px 25px;">
                    <i class="fas fa-times"></i> إلغاء
                </button>
                <button class="btn btn-success" onclick="confirmSendMessage()" id="confirmSendBtn" style="padding: 12px 25px;">
                    <i class="fas fa-paper-plane"></i> تأكيد الإرسال
                </button>
            </div>
        </div>
    </div>
    <!-- أزرار التنقل عند التمرير -->
    <button class="scroll-sidebar-btn" onclick="toggleSidebarMenu()">
        <i class="fas fa-bars"></i>
    </button>

    <button class="scroll-top-btn" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>
</body>
    <script>
        const csrfToken = "{{ csrf_token() }}";
    </script>

    <script>
        // Global variables
        // متغيرات جديدة للإرسال
        let sendMessageData = null;
        const sendConfirmModal = document.getElementById('sendConfirmModal');
        const sendConfirmModalBody = document.getElementById('sendConfirmModalBody');
        const confirmSendBtn = document.getElementById('confirmSendBtn');

        // DOM elements
        const toast = document.getElementById('toast');
        const adminMessage = document.getElementById('adminMessage');
        const selectedCount = document.getElementById('selectedCount');
        const selectionControls = document.getElementById('selectionControls');

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();

        });

        // Event Listeners
        function setupEventListeners() {
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('user-checkbox')) {
                    updateSelectedCount();
                }
            });

        }

        
        // Update selected count
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.user-checkbox:checked');
            const count = checkboxes.length;

            selectedCount.querySelector('span').textContent = count;

            if (count > 0) {
                selectionControls.style.display = 'flex';
            } else {
                selectionControls.style.display = 'none';
            }

            const tableSelectAll = document.getElementById('tableSelectAll');
            const selectAll = document.getElementById('selectAll');
            const allCheckboxes = document.querySelectorAll('.user-checkbox');
            const allChecked = allCheckboxes.length > 0 && count === allCheckboxes.length;

            if (tableSelectAll) tableSelectAll.checked = allChecked;
            if (selectAll) selectAll.checked = allChecked;
        }

        // Toggle select all
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.user-checkbox');
            const isChecked = selectAll.checked;

            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });

            document.getElementById('tableSelectAll').checked = isChecked;
            updateSelectedCount();
        }

        // Toggle table select all
        function toggleTableSelectAll() {
            const tableSelectAll = document.getElementById('tableSelectAll');
            const checkboxes = document.querySelectorAll('.user-checkbox');
            const isChecked = tableSelectAll.checked;

            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });

            document.getElementById('selectAll').checked = isChecked;
            updateSelectedCount();
        }

        // Send to all selected with confirmation
        function sendToAllSelected() {
            const message = document.getElementById('adminMessage').value.trim();
            const eventId = document.getElementById('eventSelect')?.value 
            || document.getElementById('eventIdHidden')?.value;
            const messageType = document.getElementById('messageTypeSelect')?.value 
            || document.getElementById('messageTypeHidden')?.value;
            const sendMethod = document.getElementById('sendMethodSelect').value; // جديد

            if (!message) {
                showToast('يرجى كتابة نص الرسالة', 'error');
                return;
            }

            const selectedUsers = Array.from(
                document.querySelectorAll('.user-checkbox:checked')
            ).map(cb => cb.value);

            if (selectedUsers.length === 0) {
                showToast('يرجى تحديد مستخدم واحد على الأقل', 'error');
                return;
            }

            // إعداد بيانات الإرسال
            sendMessageData = {
                user_ids: selectedUsers,
                message: message,
                event_id: eventId,
                message_type: messageType,
                send_method: sendMethod // جديد
            };

            // إعداد نص التأكيد
            const messagePreview = message.length > 100 ? message.substring(0, 100) + '...' : message;
            const methodText = sendMethod === 'with_notification' ? 
                '<span style="color: var(--success);">بإشعار (إرسال تيليجرام)</span>' : 
                '<span style="color: var(--warning);">بدون إشعار (توثيق فقط)</span>';

            sendConfirmModalBody.innerHTML = `
                <div style="margin-bottom: 20px;">
                    <strong>هل أنت متأكد من إرسال الرسالة إلى <span style="color: var(--primary);">${selectedUsers.length}</span> مستخدم؟</strong>
                </div>
                <div style="background: var(--light-gray); padding: 15px; border-radius: var(--border-radius-sm); margin-bottom: 15px; border-right: 4px solid var(--primary);">
                    <div style="margin-bottom: 5px; color: var(--dark); font-weight: 600;">
                        <i class="fas fa-envelope"></i> نص الرسالة:
                    </div>
                    <div style="color: var(--dark); line-height: 1.5;">
                        "${messagePreview}"
                    </div>
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ddd;">
                        <i class="fas fa-bell"></i> <strong>طريقة الإرسال:</strong> ${methodText}
                    </div>
                </div>
                <div style="font-size: 0.9rem; color: var(--gray);">
                    <i class="fas fa-info-circle"></i> سيتم إرسال الرسالة إلى ${selectedUsers.length} مستخدم
                </div>
                <div style="margin-top: 15px; padding: 10px; background: var(--warning-light); border-radius: var(--border-radius-sm); border-right: 3px solid var(--warning);">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>ملاحظة:</strong> ${sendMethod === 'without_notification' ? 
                        'الرسالة لن ترسل عبر تيليجرام، سيتم تسجيلها فقط في قاعدة البيانات' : 
                        'قد يستغرق إرسال الرسائل عدة دقائق حسب عدد المستخدمين المحددين'}
                </div>
            `;

            // إظهار نافذة التأكيد
            sendConfirmModal.style.display = 'flex';

            // تغيير نص زر التأكيد
            confirmSendBtn.innerHTML = sendMethod === 'without_notification' ? 
                '<i class="fas fa-save"></i> تأكيد التسجيل' : 
                '<i class="fas fa-paper-plane"></i> تأكيد الإرسال';

            setTimeout(() => {
                sendConfirmModal.querySelector('.modal-content').style.animation = 'modalSlideIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
            }, 10);
        }

        function sendTelegramMessage(userId, userName, telegramNumber) {
            const message = adminMessage.value.trim();
            const eventId = eventSelect.value;
            const messageType = messageTypeSelect.value;
            const sendMethod = document.getElementById('sendMethodSelect').value; // جديد

            if (!message) {
                showToast('الرجاء كتابة نص الرسالة', 'error');
                return;
            }

            // 🚨 تعطيل جميع أزرار الإرسال لمنع النقر المتعدد
            disableAllSendButtons();
            
            // حفظ البيانات مرة واحدة
            sendMessageData = {
                user_id: userId,
                message,
                event_id: eventId,
                message_type: messageType,
                send_method: sendMethod // جديد
            };

            // إذا كان بدون إشعار، تخطي الفحص وأرسل مباشرة
            if (sendMethod === 'without_notification') {
                sendConfirmModalBody.innerHTML = `
                    <strong>هل أنت متأكد من تسجيل الرسالة للمستخدم ${userName}؟</strong>
                    <br><br>
                    <b>رقم التليجرام:</b> ${telegramNumber || 'غير متوفر'}
                    <br>
                    <b>طريقة الإرسال:</b> <span style="color: var(--warning);">بدون إشعار (توثيق فقط)</span>
                    <br><br>
                    "${message.substring(0, 100)}..."
                    <div style="margin-top: 15px; padding: 10px; background: var(--warning-light); border-radius: var(--border-radius-sm); border-right: 3px solid var(--warning);">
                        <i class="fas fa-info-circle"></i>
                        <strong>ملاحظة:</strong> الرسالة لن ترسل عبر تيليجرام، سيتم تسجيلها فقط في قاعدة البيانات
                    </div>
                `;
                
                // 🔘 زر التأكيد
                confirmSendBtn.onclick = function () {
                    // تعطيل الزر وإظهار المؤشر
                    confirmSendBtn.disabled = true;
                    confirmSendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التسجيل...';
                    sendMessageFinal();
                };

                sendConfirmModal.style.display = 'flex';
                return;
            }


            // فحص التحذير فقط
            fetch('/admin/telegram/send-direct', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ...sendMessageData,
                    check_only: true
                })
            })
            .then(res => res.json())
            .then(data => {
                // إعادة تعيين الحالة دائمًا
                sendConfirmModal.classList.remove('warning');
                let notesHtml = '';

                if (data.notes && data.notes.length > 0) {
                    notesHtml = `
                        <div style="
                            margin-top:15px;
                            padding:12px;
                            background:#ffe5e5;
                            border-right:5px solid #dc3545;
                            border-radius:8px;
                            color:#b30000;
                            font-weight:bold;
                        ">
                            <i class="fas fa-exclamation-circle"></i>
                            ملاحظات على المستخدم:
                            <ul style="margin:8px 0 0 0; padding-right:18px;">
                                ${data.notes.map(n => `<li>${n}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                }
                // ⚠️ في حالة وجود تحذير
                if (data.status === 'warning') {
                    sendConfirmModal.classList.add('warning');
                    sendConfirmModalBody.innerHTML = `
                        <strong>⚠️ تحذير</strong>
                        <br><br>
                        ${data.warning.message}
                        ${notesHtml}
                        <hr>
                        <strong>هل أنت متأكد من إرسال الرسالة إلى ${userName}؟</strong>
                        <br><br>
                        <b>رقم التليجرام:</b> ${telegramNumber || 'غير متوفر'}
                        <br><br>
                        "${message.substring(0, 100)}..."
                    `;
                }
                // ✅ لا يوجد تحذير
                else {
                    sendConfirmModalBody.innerHTML = `
                        <strong>هل أنت متأكد من إرسال الرسالة إلى ${userName}؟</strong>
                        <br><br>
                        ${notesHtml}

                        <b>رقم التليجرام:</b> ${telegramNumber || 'غير متوفر'}
                        <br><br>
                        "${message.substring(0, 100)}..."
                    `;
                }

                // 🔘 زر التأكيد
                confirmSendBtn.onclick = function () {
                    // تعطيل الزر وإظهار المؤشر
                    confirmSendBtn.disabled = true;
                    confirmSendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإرسال...';
                    sendMessageFinal();
                };

                sendConfirmModal.style.display = 'flex';
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('حدث خطأ في التحقق', 'error');
                // إعادة تمكين الأزرار في حالة الخطأ
                enableAllSendButtons();
            });
        }

// 📋 نسخ نص الرسالة للحافظة تلقائيًا (بدون إظهار أي إشعار)
function copyMessageToClipboard(text) {
    if (!text) return;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text)
            .catch(() => {
                fallbackCopyToClipboard(text);
            });
    } else {
        fallbackCopyToClipboard(text);
    }
}

// نسخ احتياطي للمتصفحات القديمة أو غير https
function fallbackCopyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    try {
        document.execCommand('copy');
    } catch (err) {
        console.error('فشل نسخ الرسالة:', err);
    }
    document.body.removeChild(textarea);
}
        function sendMessageFinal() {
            fetch('/admin/telegram/send-direct', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ...sendMessageData,
                    force_send: true
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    sendConfirmModal.style.display = 'none';
                    copyMessageToClipboard(data.copy_message);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('حدث خطأ في الإرسال', 'error');
            })
            .finally(() => {
                // إعادة تمكين جميع أزرار الإرسال بعد الانتهاء
                enableAllSendButtons();
                // إعادة تعيين زر التأكيد
                if (confirmSendBtn) {
                    confirmSendBtn.disabled = false;
                    confirmSendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> تأكيد الإرسال';
                }
            });
        }


        // 🔒 تعطيل جميع أزرار الإرسال
        function disableAllSendButtons() {
            // تعطيل أزرار الإرسال الفردية
            const sendButtons = document.querySelectorAll('.send-btn');
            sendButtons.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإعداد...';
                btn.classList.add('disabled');
            });

            // تعطيل زر الإرسال للكل
            const sendAllBtn = document.querySelector('.send-all-btn');
            if (sendAllBtn) {
                sendAllBtn.disabled = true;
                sendAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإعداد...';
                sendAllBtn.classList.add('disabled');
            }
        }

        // 🔓 تمكين جميع أزرار الإرسال
        function enableAllSendButtons() {
            // تمكين أزرار الإرسال الفردية
            const sendButtons = document.querySelectorAll('.send-btn');
            sendButtons.forEach(btn => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> إرسال';
                btn.classList.remove('disabled');
            });

            // تمكين زر الإرسال للكل
            const sendAllBtn = document.querySelector('.send-all-btn');
            if (sendAllBtn) {
                sendAllBtn.disabled = false;
                sendAllBtn.innerHTML = '<i class="fas fa-paper-plane"></i> إرسال للكل';
                sendAllBtn.classList.remove('disabled');
            }
        }


        // Close send confirmation modal
        function closeSendConfirmModal() {
            sendConfirmModal.style.display = 'none';
            sendMessageData = null;

            // إعادة تمكين الأزرار عند إغلاق النافذة
            enableAllSendButtons();
            
            // إزالة الحدث عند الضغط خارج النافذة
            sendConfirmModal.removeEventListener('click', handleSendModalClick);
        }

        // Handle click outside send modal
        function handleSendModalClick(e) {
            if (e.target === sendConfirmModal) {
                closeSendConfirmModal();
            }
        }

        // Add event listener for send modal
        sendConfirmModal.addEventListener('click', handleSendModalClick);

        // Confirm send message
        function confirmSendMessage() {
            if (!sendMessageData) {
                closeSendConfirmModal();
                return;
            }

            // 👇 احفظ القيمة هون قبل ما ينمسح sendMessageData
            const wasWithoutNotification = sendMessageData.send_method === 'without_notification';

            confirmSendBtn.disabled = true;
            confirmSendBtn.innerHTML = wasWithoutNotification ? 
                '<i class="fas fa-spinner fa-spin"></i> جاري التسجيل...' : 
                '<i class="fas fa-spinner fa-spin"></i> جاري الإرسال...';

            event?.preventDefault();
            
            axios.post('/admin/telegram/send-direct', sendMessageData)
                .then(res => {
                    const methodText = wasWithoutNotification ? 
                        'بدون إشعار' : 'بإشعار';
                    
                    showToast(`${res.data.message} (${methodText})`, 'success');
                    
                    if (res.data.results) {
                        console.log('تفاصيل الإرسال:', res.data.results);
                    }

                    copyMessageToClipboard(res.data.copy_message);

                    closeSendConfirmModal();
                })
                .catch(err => {
                    showToast(err.response?.data?.message || 'حدث خطأ', 'error');
                })
                .finally(() => {
                    confirmSendBtn.disabled = false;
                    // 👇 استخدم المتغير المحفوظ بدل sendMessageData
                    confirmSendBtn.innerHTML = wasWithoutNotification ? 
                        '<i class="fas fa-save"></i> تأكيد التسجيل' : 
                        '<i class="fas fa-paper-plane"></i> تأكيد الإرسال';
                });
        }

        // Unblock user
        function unblockUser(userId) {
            if (!confirm('هل أنت متأكد من فك الحظر عن هذا المستخدم؟')) {
                return;
            }

            // الحصول على اللغة الحالية من URL
            const currentPath = window.location.pathname;
            const localeMatch = currentPath.match(/\/(ar|en)\//);
            const locale = localeMatch ? localeMatch[1] : 'ar'; // افتراضي عربي
            
            axios.post(`/${locale}/admin/telegram/unblock/${userId}`)
                .then(res => {
                    if (res.data.status === 'success') {
                        showToast(res.data.message, 'success');

                        // تحديث الحالة في الجدول مباشرة
                        const row = document.querySelector(`tr[data-id="${userId}"]`);
                        if (row) {
                            // تحديث السمة data-status
                            row.setAttribute('data-status', 'pending');
                            
                            // تحديث عرض الحالة
                            const statusCell = row.querySelector('[data-label="الحالة"]');
                            if (statusCell) {
                                statusCell.innerHTML = 
                                    '<span class="status-badge status-pending"><i class="fas fa-clock"></i> قيد التسجيل</span>';
                            }
                            
                            // إزالة زر فك الحظر إذا كان موجودًا
                            const unblockBtn = row.querySelector('button.btn-warning');
                            if (unblockBtn && unblockBtn.innerHTML.includes('fa-unlock')) {
                                unblockBtn.remove();
                            }
                            
                            // تحديث الإحصائيات في الشريط الجانبي
                            updateStatsAfterUnblock();
                        }
                    } else {
                        showToast(res.data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    showToast('حدث خطأ في الاتصال بالسيرفر', 'error');
                });
        }

        // دالة لتحديث الإحصائيات
        function updateStatsAfterUnblock() {
            const blockedCountElem = document.getElementById('sidebarBlockedUsers');
            const pendingCountElem = document.getElementById('sidebarPendingUsers');
            
            if (blockedCountElem && pendingCountElem) {
                let blockedCount = parseInt(blockedCountElem.textContent);
                let pendingCount = parseInt(pendingCountElem.textContent);
                
                if (!isNaN(blockedCount) && blockedCount > 0) {
                    blockedCountElem.textContent = blockedCount - 1;
                }
                if (!isNaN(pendingCount)) {
                    pendingCountElem.textContent = pendingCount + 1;
                }
            }
        }


    
    </script>

    <script>
    // Sidebar Functions
    function toggleSidebarMenu() {
        const sidebar = document.getElementById('sidebarMenu');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');

        // إضافة تأثير للنقر
        const btn = document.querySelector('.sidebar-menu-btn');
        btn.style.transform = sidebar.classList.contains('active') ? 'rotate(180deg)' : '';
    }

    // Close sidebar when clicking on a link
    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('click', toggleSidebarMenu);
    });

    // Close sidebar with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const sidebar = document.getElementById('sidebarMenu');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        }
    });

    // Smooth hover effects for sidebar items
    document.querySelectorAll('.sidebar-link, .sidebar-stat-card').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
    });

    // البديل البسيط
    let lastScrollTop = 0;

    function handleScrollSimple() {
        const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        const headerHeight = document.querySelector('.header')?.offsetHeight || 150;
        const sidebarBtn = document.querySelector('.sidebar-menu-btn');

        if (currentScroll > headerHeight) {
            // إخفاء الزر
            sidebarBtn.style.opacity = '0';
            sidebarBtn.style.transform = 'translateY(-100px)';
            sidebarBtn.style.pointerEvents = 'none';
        } else {
            // إظهار الزر
            sidebarBtn.style.opacity = '1';
            sidebarBtn.style.transform = 'translateY(0)';
            sidebarBtn.style.pointerEvents = 'auto';
        }

        lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
    }

    window.addEventListener('scroll', handleScrollSimple);
    </script>

<script>
// Toggle user dropdown menu
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('active');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('userDropdown');
    const userMenu = document.querySelector('.navbar-user');
    
    if (!userMenu.contains(event.target) && dropdown.classList.contains('active')) {
        dropdown.classList.remove('active');
    }
});


</script>
<script>
// تحسين ظهور الإشعارات في أعلى الصفحة - للجوال فقط
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    
    // تحديد الأيقونة المناسبة
    const icon = toast.querySelector('i');
    if (type === 'success') {
        icon.className = 'fas fa-check-circle';
        toast.className = 'toast toast-success show';
    } else if (type === 'error') {
        icon.className = 'fas fa-exclamation-circle';
        toast.className = 'toast toast-error show';
    } else if (type === 'warning') {
        icon.className = 'fas fa-exclamation-triangle';
        toast.className = 'toast toast-warning show';
    } else {
        icon.className = 'fas fa-info-circle';
        toast.className = 'toast toast-info show';
    }
    
    toastMessage.textContent = message;
    
    // إخفاء التوست بعد 3 ثواني
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}




// مراقبة التمرير لإظهار/إخفاء أزرار التنقل - للجوال فقط
window.addEventListener('scroll', function() {
    // تطبيق فقط على الجوال
    if (window.innerWidth <= 768) {
        const scrollBtn = document.querySelector('.scroll-top-btn');
        const mainMenuBtn = document.querySelector('.sidebar-menu-btn');
        
        if (window.scrollY > 300) {
            scrollBtn.classList.add('visible');
            // إخفاء زر القائمة الرئيسي عند التمرير لأسفل
            if (mainMenuBtn) {
                mainMenuBtn.classList.add('hidden');
            }
        } else {
            scrollBtn.classList.remove('visible');
            if (mainMenuBtn) {
                mainMenuBtn.classList.remove('hidden');
            }
        }
    }
});

// التمرير لأعلى بسلاسة
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// إضافة مستمع للأحداث للتحقق من ظهور الإشعارات
document.addEventListener('DOMContentLoaded', function() {
    // إضافة حدث للتحقق من وجود رسائل نجاح/فشل في URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        showToast('تمت العملية بنجاح', 'success');
    } else if (urlParams.has('error')) {
        showToast('حدث خطأ في العملية', 'error');
    }
});

function approveUser(userId) {
    if (!confirm('هل أنت متأكد من اعتماد هذا المستخدم؟')) {
        return;
    }

    fetch(`/admin/telegram/approve/${userId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('تم الاعتماد بنجاح');
            location.reload(); // تحديث الصفحة
        } else {
            alert('حدث خطأ');
        }
    })
    .catch(error => {
        console.error(error);
        alert('خطأ في الاتصال');
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const eventId = "{{ $eventId }}" || urlParams.get('event_id');
    const messageType = "{{ $messageType }}" || urlParams.get('message_type') || urlParams.get('type');

    if (eventId) document.getElementById('eventSelect').value = eventId;
    if (messageType) document.getElementById('messageTypeSelect').value = messageType;
});

</script>
<script>
    // Animate numbers with counter effect
    document.addEventListener('DOMContentLoaded', function() {
        // Define your data here - replace with actual values from your backend
        const statsData = {
            totalRegistered: {{ $totalUsers ?? 0 }},
            orphansCount: {{ $orphansCount ?? 0 }},
            amputationsCount: {{ $amputationsCount ?? 0 }},
            injuredCount: {{ $injuredCount ?? 0 }},
            patientsCount: {{ $patientsCount ?? 0 }},
            strandedCount: {{ $strandedCount ?? 0 }}
        };

        // Animate each counter
        Object.keys(statsData).forEach(key => {
            const element = document.getElementById(key);
            if (!element) return;
            
            const target = statsData[key];
            const duration = 1200;
            const startTime = performance.now();
            
            const updateCounter = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // EaseOutQuart for smooth animation
                const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                const currentValue = Math.floor(easeOutQuart * target);
                
                element.textContent = currentValue.toLocaleString();
                
                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    element.textContent = target.toLocaleString();
                }
            };
            
            requestAnimationFrame(updateCounter);
        });

        // Animate progress bars
        document.querySelectorAll('.stat-progress-fill').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            
            setTimeout(() => {
                bar.style.width = width;
            }, 200);
        });
    });

    // 🗑️ حذف مستخدم - للسوبر أدمن فقط
function deleteUser(userId, userName) {
    if (!confirm(`⚠️ هل أنت متأكد من حذف المستخدم "${userName}"؟\n\nهذا الإجراء لا يمكن التراجع عنه!`)) {
        return;
    }

    const deleteBtn = document.querySelector(`.delete-btn[data-user-id="${userId}"]`);
    if (deleteBtn) {
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحذف...';
    }

    // 🔥 استخدم الرابط المُولَّد من Laravel مباشرة
    const deleteUrl = `/ar/admin/telegram/${userId}`;
    
    // ⚠️ أو الأفضل: مرر الرابط من Blade
    // const deleteUrl = "{{ route('admin.telegram.destroy', ['id' => '__ID__']) }}".replace('__ID__', userId);

    axios.delete(deleteUrl, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        if (res.data.status === 'success') {
            showToast(res.data.message, 'success');
            
            const row = document.querySelector(`tr[data-id="${userId}"]`);
            if (row) {
                row.style.transition = 'all 0.5s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(-100%)';
                
                setTimeout(() => {
                    row.remove();
                    updateStatsAfterDelete();
                    
                    const remainingRows = document.querySelectorAll('#usersTableBody tr');
                    if (remainingRows.length === 0) {
                        location.reload();
                    }
                }, 500);
            }
        } else {
            showToast(res.data.message || 'حدث خطأ في الحذف', 'error');
            if (deleteBtn) {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '<i class="fas fa-trash-alt"></i> حذف';
            }
        }
    })
    .catch(err => {
        console.error('Delete Error:', err.response || err);
        
        // عرض رسالة خطأ مفصلة
        let errorMsg = 'حدث خطأ في الاتصال بالسيرفر';
        if (err.response?.data?.message) {
            errorMsg = err.response.data.message;
        } else if (err.response?.status === 403) {
            errorMsg = 'ليس لديك صلاحية للحذف';
        } else if (err.response?.status === 404) {
            errorMsg = 'المستخدم غير موجود';
        } else if (err.response?.status === 419) {
            errorMsg = 'انتهت الجلسة، يرجى إعادة تحميل الصفحة';
        }
        
        showToast(errorMsg, 'error');
        
        if (deleteBtn) {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = '<i class="fas fa-trash-alt"></i> حذف';
        }
    });
}
</script>
</html>
