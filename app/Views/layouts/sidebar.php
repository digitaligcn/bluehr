<aside class="sidebar"><div class="brand"><?php if(setting('organization_logo')): ?><img src="<?=e(setting('organization_logo'))?>" style="height:28px;margin-right:8px"><?php endif; ?><?=e(setting('organization_name','BlueHR'))?></div><nav class="menu">
<div class="menu-section">Main</div><a href="<?=url('/dashboard')?>">Dashboard</a>
<div class="menu-section">People</div><a href="<?=url('/employees')?>">Employees</a><a href="<?=url('/documents')?>">Documents</a><a href="<?=url('/organization')?>">Organization</a><a href="<?=url('/recruitment')?>">Recruitment</a><a href="<?=url('/onboarding')?>">Onboarding</a>
<div class="menu-section">Time</div><a href="<?=url('/attendance')?>">Attendance</a><a href="<?=url('/leave')?>">Leave</a><a href="<?=url('/overtime')?>">Overtime</a><a href="<?=url('/approval')?>">Approval Inbox</a>
<div class="menu-section">Payroll</div><a href="<?=url('/payroll')?>">Payroll Run</a><a href="<?=url('/payroll/salary')?>">Salary Components</a><a href="<?=url('/reimbursement')?>">Reimbursement</a><a href="<?=url('/loans')?>">Employee Loans</a><a href="<?=url('/facilities')?>">Facilities & Benefits</a>
<div class="menu-section">Talent</div><a href="<?=url('/performance')?>">Performance</a><a href="<?=url('/training')?>">Training</a><a href="<?=url('/ai')?>">AI Analytics</a>
<div class="menu-section">Integration</div><a href="<?=url('/accurate')?>">Accurate API</a><a href="<?=url('/settings/google-drive')?>">Google Drive</a><a href="<?=url('/whatsapp')?>">WhatsApp</a><a href="<?=url('/reports')?>">Reports</a>
<div class="menu-section">Admin</div><a href="<?=url('/users')?>">User Accounts</a><a href="<?=url('/settings')?>">Settings</a>
</nav></aside>
