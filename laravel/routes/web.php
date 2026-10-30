<?php

use App\Controllers\AuthController;
use App\Controllers\CrmController;
use App\Controllers\EventSuite;
use App\Controllers\GoogleCalendarController;
use Illuminate\Support\Facades\Route;

Route::get('login', [AuthController::class, 'login']);
Route::post('login', [AuthController::class, 'attempt']);
Route::get('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('forgot-password', [AuthController::class, 'sendResetLink']);
Route::get('reset-password/{token}', [AuthController::class, 'resetPassword']);
Route::post('reset-password/{token}', [AuthController::class, 'updatePassword']);
Route::get('register', [AuthController::class, 'register']);
Route::post('register', [AuthController::class, 'storeRegistration']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('legacy.auth');

Route::get('q/{code}', [EventSuite::class, 'publicQuestionnaire']);
Route::post('q/{code}', [EventSuite::class, 'submitPublicQuestionnaire']);
Route::get('kuesioner/{code}', [EventSuite::class, 'publicQuestionnaire']);
Route::post('kuesioner/{code}', [EventSuite::class, 'submitPublicQuestionnaire']);

Route::get('crm/login', [CrmController::class, 'login']);
Route::post('crm/login', [CrmController::class, 'attempt']);
Route::post('crm/register', [CrmController::class, 'storeRegistration'])->middleware('role:Kepala Kampus');
Route::get('crm/template', [CrmController::class, 'downloadTemplate']);
Route::prefix('crm')->middleware('crm.auth')->group(function (): void {
    Route::get('/', [CrmController::class, 'index']);
    Route::get('monitoring', [CrmController::class, 'agentMonitoring']);
    Route::get('export-excel', [CrmController::class, 'exportExcel']);
    Route::post('logout', [CrmController::class, 'logout']);
    Route::post('users', [CrmController::class, 'storeUser']);
    Route::post('users/{id}/delete', [CrmController::class, 'deleteUser'])->whereNumber('id');
    Route::post('leads/upload', [CrmController::class, 'uploadLeads']);
    Route::post('leads/assign', [CrmController::class, 'assignLeads']);
    Route::post('leads/auto-assign', [CrmController::class, 'autoAssignLeads']);
    Route::post('leads/{id}/update', [CrmController::class, 'updateLead'])->whereNumber('id');
    Route::post('leads/{id}/delete', [CrmController::class, 'deleteLead'])->whereNumber('id');
    Route::get('leads/{id}/chat', [CrmController::class, 'leadChatData'])->whereNumber('id');
    Route::post('leads/{id}/chat', [CrmController::class, 'storeChatMessage'])->whereNumber('id');
    Route::post('leads/{id}/save-chat-image', [CrmController::class, 'saveChatAsImage'])->whereNumber('id');
    Route::post('ai/assist', [CrmController::class, 'aiAssist']);
    Route::get('logs/{id}/image', [CrmController::class, 'followUpImage'])->whereNumber('id');
    Route::post('batches/{id}/delete', [CrmController::class, 'deleteBatch'])->whereNumber('id');
});

Route::middleware('legacy.auth')->group(function (): void {
    Route::get('/', [EventSuite::class, 'dashboard'])->middleware('access:dashboard');
    Route::get('dashboard', [EventSuite::class, 'dashboard'])->middleware('access:dashboard');
    Route::get('task-manager', [EventSuite::class, 'taskManager'])->middleware('access:dashboard');
    Route::get('task-manager/new', [EventSuite::class, 'newTask'])->middleware('access:dashboard');
    Route::post('task-manager/tasks', [EventSuite::class, 'storeTask'])->middleware('access:dashboard');

    Route::get('events', [EventSuite::class, 'events'])->middleware('access:events');
    Route::get('events/export', [EventSuite::class, 'exportEvents'])->middleware('role:Admin Sistem');
    Route::get('events/template', [EventSuite::class, 'downloadEventImportTemplate'])->middleware('role:Admin Sistem');
    Route::post('events/import', [EventSuite::class, 'importEvents'])->middleware('role:Admin Sistem');
    Route::get('events/new', [EventSuite::class, 'newEvent'])->middleware('access:events');
    Route::post('events', [EventSuite::class, 'storeEvent'])->middleware('access:events');
    Route::get('events/{code}/edit', [EventSuite::class, 'editEvent'])->middleware('access:events');
    Route::post('events/{code}/update', [EventSuite::class, 'updateEvent'])->middleware('access:events');
    Route::post('events/{code}/delete', [EventSuite::class, 'deleteEvent'])->middleware('access:events');
    Route::post('events/{code}/submit', [EventSuite::class, 'submitDraftEvent'])->middleware('access:events');
    Route::post('events/{code}/checklist', [EventSuite::class, 'updateChecklist'])->middleware('access:events');
    Route::post('events/{code}/checklist/items', [EventSuite::class, 'storeChecklistItem'])->middleware('access:events');
    Route::post('events/{code}/checklist/items/{itemId}/delete', [EventSuite::class, 'deleteChecklistItem'])->middleware('access:events')->whereNumber('itemId');
    Route::post('events/{code}/documents/{type}/upload', [EventSuite::class, 'uploadEventDocument'])->middleware('access:events');
    Route::get('events/{code}/documents/{type}/file', [EventSuite::class, 'downloadUploadedEventDocument'])->middleware('access:events');
    Route::post('events/{code}/questionnaire', [EventSuite::class, 'saveEventQuestionnaire'])->middleware('access:events');
    Route::get('events/{code}/questionnaire/export', [EventSuite::class, 'exportQuestionnaireResponses'])->middleware('access:events');
    Route::get('events/{code}', [EventSuite::class, 'eventDetail'])->middleware('access:events');
    Route::post('checklist-tasks/{itemId}/report', [EventSuite::class, 'reportChecklistTask'])->middleware('access:dashboard')->whereNumber('itemId');

    Route::get('calendar', [EventSuite::class, 'calendar'])->middleware('access:calendar');
    Route::get('master-data', [EventSuite::class, 'masterData'])->middleware('role:Admin Sistem');
    Route::post('master-data/campuses', [EventSuite::class, 'storeCampus'])->middleware('role:Admin Sistem');
    Route::post('master-data/campuses/{id}/update', [EventSuite::class, 'updateCampus'])->middleware('role:Admin Sistem')->whereNumber('id');
    Route::post('master-data/campuses/{id}/delete', [EventSuite::class, 'deleteCampus'])->middleware('role:Admin Sistem')->whereNumber('id');
    Route::post('master-data/categories', [EventSuite::class, 'storeEventCategory'])->middleware('role:Admin Sistem');
    Route::post('master-data/categories/{id}/update', [EventSuite::class, 'updateEventCategory'])->middleware('role:Admin Sistem')->whereNumber('id');
    Route::post('master-data/categories/{id}/delete', [EventSuite::class, 'deleteEventCategory'])->middleware('role:Admin Sistem')->whereNumber('id');

    Route::get('google-calendar/connect', [GoogleCalendarController::class, 'connect'])->middleware('role:Admin Sistem,Event Creator');
    Route::get('google-calendar/callback', [GoogleCalendarController::class, 'callback'])->middleware('role:Admin Sistem,Event Creator');
    Route::post('google-calendar/sync', [GoogleCalendarController::class, 'syncApproved'])->middleware('role:Admin Sistem,Event Creator');
    Route::post('google-calendar/import', [GoogleCalendarController::class, 'importMonth'])->middleware('role:Admin Sistem,Event Creator');
    Route::get('google-calendar/export', [GoogleCalendarController::class, 'exportMonth'])->middleware('role:Admin Sistem,Event Creator');
    Route::post('google-calendar/sync/{code}', [GoogleCalendarController::class, 'syncEvent'])->middleware('role:Admin Sistem,Event Creator');

    Route::get('approvals', [EventSuite::class, 'approvals'])->middleware('access:approvals');
    Route::post('approvals/{code}/approve', [EventSuite::class, 'approveEvent'])->middleware('role:Admin Sistem');
    Route::post('approvals/{code}/reject', [EventSuite::class, 'rejectEvent'])->middleware('role:Admin Sistem');

    Route::get('documents', [EventSuite::class, 'documents'])->middleware('access:documents');
    Route::get('documents/notification/open', [EventSuite::class, 'openDocumentNotification'])->middleware('access:documents');
    Route::get('documents/generate', [EventSuite::class, 'aiGenerateDocument'])->middleware('access:documents');
    Route::post('documents/generate/process', [EventSuite::class, 'aiProcessGenerateDocument'])->middleware('access:documents');
    Route::post('documents/generate/save', [EventSuite::class, 'aiSaveGeneratedDocument'])->middleware('access:documents');
    Route::get('documents/generate/export-word', [EventSuite::class, 'aiExportWordDocument'])->middleware('access:documents');
    Route::get('documents/generate/export-pdf', [EventSuite::class, 'aiExportPdfDocument'])->middleware('access:documents');
    Route::get('documents/{type}', [EventSuite::class, 'documents'])->middleware('access:documents');
    Route::get('documents/{code}/{type}/preview', [EventSuite::class, 'previewDocument'])->middleware('access:documents');
    Route::get('documents/{code}/{type}/download', [EventSuite::class, 'downloadDocument'])->middleware('access:documents');
    Route::post('documents/{code}/{type}/template', [EventSuite::class, 'uploadDocumentTemplate'])->middleware('role:Admin Sistem');
    Route::post('documents/{code}/{type}/link', [EventSuite::class, 'updateDocumentLink'])->middleware('role:Admin Sistem');
    Route::post('documents/{code}/{type}/delete', [EventSuite::class, 'deleteDocumentLink'])->middleware('role:Admin Sistem');

    Route::get('document-templates', [EventSuite::class, 'documentTemplates'])->middleware('access:documents');
    Route::post('document-templates/create', [EventSuite::class, 'createDocumentTemplate'])->middleware('role:Admin Sistem');
    Route::post('document-templates/{id}/update', [EventSuite::class, 'updateDocumentTemplate'])->middleware('role:Admin Sistem');
    Route::post('document-templates/{id}/delete', [EventSuite::class, 'deleteDocumentTemplate'])->middleware('role:Admin Sistem');

    Route::get('generate-documents', [EventSuite::class, 'generateDocuments'])->middleware('access:documents');
    Route::get('generate-documents/{type}', [EventSuite::class, 'generateDocuments'])->middleware('access:documents');
    Route::post('generate-documents/{code}/{type}/generate', [EventSuite::class, 'generateDocument'])->middleware('access:documents');
    Route::get('generate-documents/{code}/{type}/preview', [EventSuite::class, 'previewDocument'])->middleware('access:documents');
    Route::get('generate-documents/{code}/{type}/download', [EventSuite::class, 'downloadDocument'])->middleware('access:documents');

    Route::get('reports', [EventSuite::class, 'reports'])->middleware('access:reports');
    Route::get('laporan-crm', [EventSuite::class, 'crmRecommendationReport'])->middleware('access:dashboard');
    Route::get('crm-report', [EventSuite::class, 'crmRecommendationReport'])->middleware('access:dashboard');
    Route::get('reports/export', [EventSuite::class, 'exportReports'])->middleware('access:reports');
    Route::get('reports/crm-export', [EventSuite::class, 'exportCrmResults'])->middleware('access:reports');
    Route::post('reports/{code}', [EventSuite::class, 'updateReport'])->middleware('access:reports');

    Route::get('one-campus', [EventSuite::class, 'oneCampus'])->middleware('access:one-campus');
    Route::get('one-campus/ojt', [EventSuite::class, 'oneCampusOjt'])->middleware('access:one-campus');
    Route::get('one-campus/schools', [EventSuite::class, 'oneCampusSchools'])->middleware('access:one-campus');
    Route::get('one-campus/banners', [EventSuite::class, 'oneCampusBanners'])->middleware('access:one-campus');
    Route::get('one-campus/ojt/export', [EventSuite::class, 'exportOneCampusOjt'])->middleware('access:one-campus');
    Route::get('one-campus/schools/export', [EventSuite::class, 'exportOneCampusSchools'])->middleware('access:one-campus');
    Route::get('one-campus/banners/export', [EventSuite::class, 'exportOneCampusBanners'])->middleware('access:one-campus');
    Route::post('one-campus/schools', [EventSuite::class, 'storeOneCampusSchool'])->middleware('access:one-campus');
    Route::post('one-campus/schools/{id}/update', [EventSuite::class, 'updateOneCampusSchool'])->middleware('access:one-campus')->whereNumber('id');
    Route::post('one-campus/schools/{id}/delete', [EventSuite::class, 'deleteOneCampusSchool'])->middleware('access:one-campus')->whereNumber('id');
    Route::post('one-campus/banners', [EventSuite::class, 'storeOneCampusBanner'])->middleware('access:one-campus');
    Route::post('one-campus/banners/{id}/update', [EventSuite::class, 'updateOneCampusBanner'])->middleware('access:one-campus')->whereNumber('id');
    Route::post('one-campus/banners/{id}/delete', [EventSuite::class, 'deleteOneCampusBanner'])->middleware('access:one-campus')->whereNumber('id');
    Route::post('one-campus/ojt', [EventSuite::class, 'storeOneCampusOjt'])->middleware('access:one-campus');
    Route::post('one-campus/ojt/{id}/update', [EventSuite::class, 'updateOneCampusOjt'])->middleware('access:one-campus')->whereNumber('id');
    Route::post('one-campus/ojt/{id}/delete', [EventSuite::class, 'deleteOneCampusOjt'])->middleware('access:one-campus')->whereNumber('id');

    Route::get('analytics', [EventSuite::class, 'analytics'])->middleware('access:analytics');
    Route::get('analisis-khusus', [EventSuite::class, 'specialAnalytics'])->middleware('role:Admin Sistem,DMER');
    Route::get('analisis-khusus/laporan-strategi', [EventSuite::class, 'exportSpecialStrategyReport'])->middleware('role:Admin Sistem,DMER');
    Route::get('notifications', [EventSuite::class, 'notifications'])->middleware('access:notifications');
    Route::post('notifications/read-all', [EventSuite::class, 'markNotificationsRead'])->middleware('access:notifications');

    Route::get('users', [EventSuite::class, 'users'])->middleware('role:Admin Sistem');
    Route::match(['get', 'post'], 'impersonate/leave', [EventSuite::class, 'leaveImpersonation']);
    Route::match(['get', 'post'], 'impersonate/{id}', [EventSuite::class, 'impersonateUser'])->whereNumber('id');
    Route::post('users', [EventSuite::class, 'storeUser'])->middleware('role:Admin Sistem');
    Route::post('users/{id}/update', [EventSuite::class, 'updateUser'])->middleware('role:Admin Sistem')->whereNumber('id');
    Route::post('users/{id}/delete', [EventSuite::class, 'deleteUser'])->middleware('role:Admin Sistem')->whereNumber('id');
    Route::post('users/{id}/force-delete', [EventSuite::class, 'forceDeleteUser'])->middleware('role:Admin Sistem')->whereNumber('id');
    Route::post('users/{id}/restore', [EventSuite::class, 'restoreUser'])->middleware('role:Admin Sistem')->whereNumber('id');
    Route::post('users/{id}/reset-password', [EventSuite::class, 'resetUserPassword'])->middleware('role:Admin Sistem')->whereNumber('id');
    Route::post('admin/crm-users', [EventSuite::class, 'storeCrmUser'])->middleware('role:Admin Sistem');
    Route::post('admin/crm-users/{id}/update', [EventSuite::class, 'updateCrmUser'])->middleware('role:Admin Sistem')->whereNumber('id');
    Route::post('admin/crm-users/{id}/delete', [EventSuite::class, 'deleteCrmUser'])->middleware('role:Admin Sistem')->whereNumber('id');
    Route::post('admin/crm-users/{id}/force-delete', [EventSuite::class, 'forceDeleteCrmUser'])->middleware('role:Admin Sistem')->whereNumber('id');
    Route::post('admin/crm-users/{id}/restore', [EventSuite::class, 'restoreCrmUser'])->middleware('role:Admin Sistem')->whereNumber('id');
    Route::post('admin/crm-users/{id}/reset-password', [EventSuite::class, 'resetCrmUserPassword'])->middleware('role:Admin Sistem')->whereNumber('id');
    Route::post('roles', [EventSuite::class, 'storeRole'])->middleware('role:Admin Sistem');
    Route::post('roles/{id}/update', [EventSuite::class, 'updateRole'])->middleware('role:Admin Sistem')->whereNumber('id');
    Route::post('roles/{id}/delete', [EventSuite::class, 'deleteRole'])->middleware('role:Admin Sistem')->whereNumber('id');
});
