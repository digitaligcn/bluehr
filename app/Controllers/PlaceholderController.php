<?php
namespace BlueHR\Controllers;
use BlueHR\Core\Controller;
use BlueHR\Core\Database;

class PlaceholderController extends Controller {
    private function module(string $title, string $description, array $features=[]): void {
        $this->view('module/index', ['title'=>$title, 'description'=>$description, 'features'=>$features]);
    }
    public function recruitment(): void { $this->view('recruitment/index', ['title'=>'Recruitment']); }
    public function onboarding(): void { $this->view('onboarding/index', ['title'=>'Onboarding']); }
    public function reimbursement(): void { $this->view('reimbursement/index', ['title'=>'Reimbursement']); }
    public function loans(): void { $this->view('loans/index', ['title'=>'Employee Loans']); }
    public function performance(): void { $this->view('performance/index', ['title'=>'Performance']); }
    public function training(): void { $this->view('training/index', ['title'=>'Training']); }
    public function reports(): void { $this->view('reports/index', ['title'=>'Reports']); }
    public function settings(): void { $this->view('settings/index', ['title'=>'Settings']); }
    public function approval(): void { $this->module('Approval Inbox & Request Types','Generic approval engine for leave, overtime, procurement, duty trip, reimbursement and custom requests.', ['Configurable request types and dynamic fields','Approval flow by user, role, manager, department','Requester and approver email notification queue','Approver inbox for pending follow-up']); }
    public function facilities(): void { $this->module('Facilities, Benefits & Eligibility','Manage insurance, benefits, office facilities and eligibility rules by job level or employment type.', ['Facility/benefit master data','Eligibility rules by grade/job level','Employee facility assignment with validity period','Report for eligible employees and exceptions']); }
    public function documents(): void { $this->module('Employee Documents & CV Upload','Upload CV and supporting files to local storage or configured Google Drive folders.', ['CV / employee docs / supporting files','Google Drive folder routing','Metadata, notes and uploader audit','Employee and candidate attachment support']); }
    public function ai(): void { $this->module('AI People Analytics','Controlled AI access for employee analysis, attendance trends, payroll anomalies, performance summaries and CV/resume summarization.', ['Role-based AI permissions','Audit log for every prompt','Data scope controls and privacy safeguards','Natural-language HR query foundation']); }
    public function whatsapp(): void { $this->module('WhatsApp Contact','Store WhatsApp numbers and quick-contact templates for employees, candidates and emergency contacts.', ['wa.me quick links','Phone normalization 08 -> 628','Message templates for HR reminders','Employee/candidate/emergency contact support']); }
    public function googleDrive(): void { $this->module('Google Drive Settings','Configure Google Drive folders for CV, employee documents and supporting files.', ['CV Folder ID','Employee Document Folder ID','Supporting File Folder ID','Encrypted OAuth credentials / refresh token hook']); }
}
