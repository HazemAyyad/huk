// // Global variables
//         let usersToDelete = [];
//         let isBulkDelete = false;
//         // متغيرات جديدة للإرسال
//         let sendUserId = null;
//         let sendMessageData = null;
//         const sendConfirmModal = document.getElementById('sendConfirmModal');
//         const sendConfirmModalBody = document.getElementById('sendConfirmModalBody');
//         const confirmSendBtn = document.getElementById('confirmSendBtn');

//         // DOM elements
//         const toast = document.getElementById('toast');
//         const adminMessage = document.getElementById('adminMessage');
//         const selectedCount = document.getElementById('selectedCount');
//         const selectionControls = document.getElementById('selectionControls');
//         const deleteModal = document.getElementById('deleteModal');
//         const deleteModalBody = document.getElementById('deleteModalBody');
//         const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');

//         // Initialize the page
//         document.addEventListener('DOMContentLoaded', function() {
//             setupEventListeners();

//             // إضافة labels للجدول للاستجابة على الهواتف
//         });

//         // إعداد labels للجدول


//         // Event Listeners
//         function setupEventListeners() {
//             document.addEventListener('change', function(e) {
//                 if (e.target.classList.contains('user-checkbox')) {
//                     updateSelectedCount();
//                 }
//             });

//             deleteModal.addEventListener('click', function(e) {
//                 if (e.target === deleteModal) closeDeleteModal();
//             });
//         }

//         // Open Telegram chat
//         function openTelegramChat(username) {
//             if (!username) {
//                 showToast('اسم المستخدم غير متوفر', 'error');
//                 return;
//             }

//             // const telegramUrl = `https://t.me/${username}`;
//             // window.location.href = telegramUrl;

//             setTimeout(() => {
//                 window.open(`https://t.me/${username}`, '_blank');
//             }, 200);
//         }

//         // Filter users


//         // Render table

//         // Sort users


//         // Sort table

//         // Render pagination


//         // Reset filters
//         // Update selected count
//         function updateSelectedCount() {
//             const checkboxes = document.querySelectorAll('.user-checkbox:checked');
//             const count = checkboxes.length;

//             selectedCount.querySelector('span').textContent = count;

//             if (count > 0) {
//                 selectionControls.style.display = 'flex';
//                 deleteSelectedBtn.style.display = 'inline-flex';
//             } else {
//                 selectionControls.style.display = 'none';
//                 deleteSelectedBtn.style.display = 'none';
//             }

//             const tableSelectAll = document.getElementById('tableSelectAll');
//             const selectAll = document.getElementById('selectAll');
//             const allCheckboxes = document.querySelectorAll('.user-checkbox');
//             const allChecked = allCheckboxes.length > 0 && count === allCheckboxes.length;

//             if (tableSelectAll) tableSelectAll.checked = allChecked;
//             if (selectAll) selectAll.checked = allChecked;
//         }

//         // Toggle select all
//         function toggleSelectAll() {
//             const selectAll = document.getElementById('selectAll');
//             const checkboxes = document.querySelectorAll('.user-checkbox');
//             const isChecked = selectAll.checked;

//             checkboxes.forEach(checkbox => {
//                 checkbox.checked = isChecked;
//             });

//             document.getElementById('tableSelectAll').checked = isChecked;
//             updateSelectedCount();
//         }

//         // Toggle table select all
//         function toggleTableSelectAll() {
//             const tableSelectAll = document.getElementById('tableSelectAll');
//             const checkboxes = document.querySelectorAll('.user-checkbox');
//             const isChecked = tableSelectAll.checked;

//             checkboxes.forEach(checkbox => {
//                 checkbox.checked = isChecked;
//             });

//             document.getElementById('selectAll').checked = isChecked;
//             updateSelectedCount();
//         }

//         // Send to all selected
//         // Send to all selected with confirmation
//         function sendToAllSelected() {
//             const message = document.getElementById('adminMessage').value.trim();
//             const eventId = document.getElementById('eventSelect').value;
//             const messageType = document.getElementById('messageTypeSelect').value;

//             if (!message) {
//                 showToast('يرجى كتابة نص الرسالة', 'error');
//                 return;
//             }

//             const selectedUsers = Array.from(
//                 document.querySelectorAll('.user-checkbox:checked')
//             ).map(cb => cb.value);

//             if (selectedUsers.length === 0) {
//                 showToast('يرجى تحديد مستخدم واحد على الأقل', 'error');
//                 return;
//             }

//             // إعداد بيانات الإرسال
//             sendMessageData = {
//                 user_ids: selectedUsers,
//                 message: message,
//                 event_id: eventId,
//                 message_type: messageType
//             };

//             // إعداد نص التأكيد
//             const messagePreview = message.length > 100 ? message.substring(0, 100) + '...' : message;

//             sendConfirmModalBody.innerHTML = `
//                 <div style="margin-bottom: 20px;">
//                     <strong>هل أنت متأكد من إرسال الرسالة إلى <span style="color: var(--primary);">${selectedUsers.length}</span> مستخدم؟</strong>
//                 </div>
//                 <div style="background: var(--light-gray); padding: 15px; border-radius: var(--border-radius-sm); margin-bottom: 15px; border-right: 4px solid var(--primary);">
//                     <div style="margin-bottom: 10px; color: var(--dark); font-weight: 600;">
//                         <i class="fas fa-envelope"></i> نص الرسالة:
//                     </div>
//                     <div style="color: var(--dark); line-height: 1.5;">
//                         "${messagePreview}"
//                     </div>
//                 </div>
//                 <div style="font-size: 0.9rem; color: var(--gray);">
//                     <i class="fas fa-info-circle"></i> سيتم إرسال الرسالة إلى ${selectedUsers.length} مستخدم
//                 </div>
//                 <div style="margin-top: 15px; padding: 10px; background: var(--warning-light); border-radius: var(--border-radius-sm); border-right: 3px solid var(--warning);">
//                     <i class="fas fa-exclamation-triangle"></i>
//                     <strong>ملاحظة:</strong> قد يستغرق إرسال الرسائل عدة دقائق حسب عدد المستخدمين المحددين
//                 </div>
//             `;

//             // إظهار نافذة التأكيد
//             sendConfirmModal.style.display = 'flex';

//             // تغيير نص زر التأكيد
//             confirmSendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> تأكيد الإرسال';

//             setTimeout(() => {
//                 sendConfirmModal.querySelector('.modal-content').style.animation = 'modalSlideIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
//             }, 10);
//         }

//         // Send telegram message
//         // Send telegram message with confirmation
//         function sendTelegramMessage(userId, userName, telegramNumber) {
//             const message = adminMessage.value.trim();
//             const eventId = eventSelect.value;
//             const messageType = messageTypeSelect.value;

//             if (!message) {
//                 showToast('الرجاء كتابة نص الرسالة', 'error');
//                 return;
//             }

//             sendMessageData = {
//                 user_id: userId,
//                 message,
//                 event_id: eventId,
//                 message_type: messageType
//             };

//             sendConfirmModalBody.innerHTML = `
//                 <strong>هل أنت متأكد من إرسال الرسالة إلى ${userName}؟</strong>
//                 <br><br>
//                 <b>رقم التليجرام:</b> ${telegramNumber || 'غير متوفر'}
//                 <br><br>
//                 "${message.substring(0, 100)}..."
//             `;

//             sendConfirmModal.style.display = 'flex';
//         }


//         // Close send confirmation modal
//         function closeSendConfirmModal() {
//             sendConfirmModal.style.display = 'none';
//             sendUserId = null;
//             sendMessageData = null;

//             // إزالة الحدث عند الضغط خارج النافذة
//             sendConfirmModal.removeEventListener('click', handleSendModalClick);
//         }

//         // Handle click outside send modal
//         function handleSendModalClick(e) {
//             if (e.target === sendConfirmModal) {
//                 closeSendConfirmModal();
//             }
//         }

//         // Add event listener for send modal
//         sendConfirmModal.addEventListener('click', handleSendModalClick);

//         // Confirm send message
//         function confirmSendMessage() {
//             if (!sendMessageData) {
//                 closeSendConfirmModal();
//                 return;
//             }

//             // تعطيل زر التأكيد أثناء الإرسال
//             confirmSendBtn.disabled = true;
//             confirmSendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإرسال...';

//             axios.post('/admin/telegram/send-direct', sendMessageData)
//             .then(res => {
//                 showToast(res.data.message, 'success');

//                 // تنظيف التخزين


//                 closeSendConfirmModal();
//             })

//             .catch(err => {
//                 showToast(err.response?.data?.message || 'حدث خطأ', 'error');
//             })
//             .finally(() => {
//                 // إعادة تمكين الزر
//                 confirmSendBtn.disabled = false;
//                 confirmSendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> تأكيد الإرسال';
//             });
//         }

//         // Unblock user
//         function unblockUser(userId) {
//             if (!confirm('هل أنت متأكد من فك الحظر عن هذا المستخدم؟')) {
//                 return;
//             }

//             axios.post(`/admin/telegram/unblock/${userId}`)
//                 .then(res => {
//                     showToast(res.data.message, 'success');

//                     // تحديث الحالة في الجدول مباشرة
//                     const row = document.querySelector(`tr[data-id="${userId}"]`);
//                     if (row) {
//                         row.querySelector('[data-label="الحالة"]').innerHTML =
//                             '<span class="status-badge status-pending"><i class="fas fa-clock"></i> قيد التسجيل</span>';
//                         // إزالة زر فك الحظر
//                         const btn = row.querySelector('button.btn-warning');
//                         if (btn) btn.remove();
//                     }
//                 })
//                 .catch(err => {
//                     showToast(err.response?.data?.message || 'حدث خطأ أثناء فك الحظر', 'error');
//                 });
//         }

//         // Delete user
//         function deleteUser(userId) {
//             usersToDelete = [userId];
//             isBulkDelete = false;

//             const user = allUsers.find(u => u.id == userId);
//             const userName = user ? (user.full_name || user.first_name || 'المستخدم') : '';

//             deleteModalBody.innerHTML = `
//                 هل أنت متأكد من حذف المستخدم <strong>${userName}</strong>؟<br><br>
//                 <small style="color: var(--danger);">⚠️ هذا الإجراء لا يمكن التراجع عنه وسيتم حذف جميع بيانات المستخدم بشكل نهائي.</small>
//             `;

//             deleteModal.style.display = 'flex';
//         }

//         // Delete selected users
//         function deleteSelectedUsers() {
//             const checkboxes = document.querySelectorAll('.user-checkbox:checked');

//             if (checkboxes.length === 0) {
//                 showToast('الرجاء تحديد مستخدم واحد على الأقل', 'error');
//                 return;
//             }

//             usersToDelete = Array.from(checkboxes).map(cb => parseInt(cb.value));
//             isBulkDelete = true;

//             deleteModalBody.innerHTML = `
//                 هل أنت متأكد من حذف ${usersToDelete.length} مستخدم؟<br><br>
//                 <small style="color: var(--danger);">⚠️ هذا الإجراء لا يمكن التراجع عنه وسيتم حذف جميع بيانات المستخدمين المحددين بشكل نهائي.</small>
//             `;

//             deleteModal.style.display = 'flex';
//         }

//         // Close delete modal
//         function closeDeleteModal() {
//             deleteModal.style.display = 'none';
//             usersToDelete = [];
//         }

//         // Confirm delete
//         async function confirmDelete() {
//             try {
//                 showToast('جاري حذف المستخدمين...', 'warning');

//                 const response = await fetch("{{ route('admin.telegram.delete-users') }}", {
//                     method: 'POST',
//                     headers: {
//                         'Content-Type': 'application/json',
//                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
//                     },
//                     body: JSON.stringify({
//                         user_ids: usersToDelete
//                     })
//                 });

//                 const data = await response.json();

//                 if (data.status === 'success') {
//                     allUsers = allUsers.filter(user => !usersToDelete.includes(user.id));
//                     filteredUsers = filteredUsers.filter(user => !usersToDelete.includes(user.id));

//                     if (filteredUsers.length <= (currentPage - 1) * itemsPerPage && currentPage > 1) {
//                         currentPage--;
//                     }

//                     showToast(isBulkDelete ?
//                         `تم حذف ${usersToDelete.length} مستخدم بنجاح` :
//                         'تم حذف المستخدم بنجاح', 'success');

//                 } else {
//                     showToast('فشل حذف المستخدمين', 'error');
//                 }
//             } catch (error) {
//                 showToast('فشل الاتصال بالسيرفر', 'error');
//             }

//             closeDeleteModal();
//         }

//         // Update stats

//         // Show toast
//         function showToast(message, type = 'success') {
//             const toast = document.getElementById('toast');
//             toast.className = `toast toast-${type}`;

//             const icon = toast.querySelector('i');
//             if (type === 'success') icon.className = 'fas fa-check-circle';
//             else if (type === 'error') icon.className = 'fas fa-exclamation-circle';
//             else icon.className = 'fas fa-info-circle';

//             document.getElementById('toastMessage').textContent = message;

//             toast.classList.add('show');

//             setTimeout(() => {
//                 toast.classList.remove('show');
//             }, 3000);
//         }

//         document.getElementById('eventSelect').addEventListener('change', function () {
//             const selectedOption = this.options[this.selectedIndex];
//             const defaultMessage = selectedOption.getAttribute('data-message') || '';

//             document.getElementById('adminMessage').value = defaultMessage;

//             localStorage.setItem('eventSelect', this.value);
//             localStorage.setItem('adminMessage', defaultMessage);
//         });

//         function saveMessageState() {
//             localStorage.setItem('eventSelect', document.getElementById('eventSelect').value);
//             localStorage.setItem('messageTypeSelect', document.getElementById('messageTypeSelect').value);
//             localStorage.setItem('adminMessage', document.getElementById('adminMessage').value);
//         }

//         document.getElementById('messageTypeSelect').addEventListener('change', saveMessageState);
//         document.getElementById('adminMessage').addEventListener('input', saveMessageState);

//         document.addEventListener('DOMContentLoaded', function () {

//             const eventValue = localStorage.getItem('eventSelect');
//             const messageTypeValue = localStorage.getItem('messageTypeSelect');
//             const messageValue = localStorage.getItem('adminMessage');

//             if (eventValue) {
//                 document.getElementById('eventSelect').value = eventValue;
//             }

//             if (messageTypeValue) {
//                 document.getElementById('messageTypeSelect').value = messageTypeValue;
//             }

//             if (eventValue && messageValue) {
//                 document.getElementById('adminMessage').value = messageValue;
//             }
//         });
//     // Sidebar Menu Functions
//     // Sidebar Functions
//     function toggleSidebarMenu() {
//         const sidebar = document.getElementById('sidebarMenu');
//         const overlay = document.getElementById('sidebarOverlay');
//         sidebar.classList.toggle('active');
//         overlay.classList.toggle('active');

//         // إضافة تأثير للنقر
//         const btn = document.querySelector('.sidebar-menu-btn');
//         btn.style.transform = sidebar.classList.contains('active') ? 'rotate(180deg)' : '';
//     }

//     // Update time in sidebar footer
//     function updateSidebarTime() {
//         const now = new Date();
//         const timeString = now.toLocaleTimeString('ar-SA', {
//             hour: '2-digit',
//             minute: '2-digit',
//             second: '2-digit',
//             hour12: true
//         });
//         const dateString = now.toLocaleDateString('ar-SA', {
//             weekday: 'long',
//             year: 'numeric',
//             month: 'long',
//             day: 'numeric'
//         });

//         document.getElementById('currentTime').textContent = `${timeString} - ${dateString}`;
//     }

//     // Initialize time and update every second
//     updateSidebarTime();
//     setInterval(updateSidebarTime, 1000);

//     // Close sidebar when clicking on a link
//     document.querySelectorAll('.sidebar-link').forEach(link => {
//         link.addEventListener('click', toggleSidebarMenu);
//     });

//     // Close sidebar with Escape key
//     document.addEventListener('keydown', function(e) {
//         if (e.key === 'Escape') {
//             const sidebar = document.getElementById('sidebarMenu');
//             const overlay = document.getElementById('sidebarOverlay');
//             if (sidebar.classList.contains('active')) {
//                 sidebar.classList.remove('active');
//                 overlay.classList.remove('active');
//             }
//         }
//     });

//     // Smooth hover effects for sidebar items
//     document.querySelectorAll('.sidebar-link, .sidebar-stat-card').forEach(item => {
//         item.addEventListener('mouseenter', function() {
//             this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
//         });
//     });
