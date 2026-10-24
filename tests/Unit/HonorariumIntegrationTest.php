<?php
use App\Models\{DefenseRequest,FinanceDocumentSubmission,HonorariumActivity,Notification,User};
use App\Services\FinanceDocumentWorkflowService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema,Mail,Http};
use Inertia\Testing\AssertableInertia as Assert;
uses(Tests\TestCase::class);
beforeEach(function(){
    $this->withoutVite();
    Schema::create('users',function(Blueprint $t){$t->id();foreach(['first_name','middle_name','last_name','email','role','school_id','program'] as $f)$t->string($f)->nullable();$t->timestamp('email_verified_at')->nullable();$t->timestamps();});
    Schema::create('advisers',function(Blueprint $t){$t->id();$t->unsignedBigInteger('user_id');$t->string('status');});
    Schema::create('panelists',function(Blueprint $t){$t->id();$t->string('name');$t->string('email');});
    Schema::create('payment_rates',function(Blueprint $t){$t->id();foreach(['program_level','type','defense_type'] as $f)$t->string($f);$t->decimal('amount',10,2);});
    Schema::create('honorarium_payments',function(Blueprint $t){$t->id();$t->unsignedBigInteger('defense_request_id');});
    Schema::create('defense_requests',function(Blueprint $t){$t->id();foreach(['submitted_by','adviser_user_id','coordinator_user_id','defense_chairperson','defense_panelist1','defense_panelist2','defense_panelist3','defense_panelist4'] as $f)$t->unsignedBigInteger($f)->nullable();foreach(['thesis_title','first_name','last_name','school_id','program','workflow_state','status','proof_of_payment','defense_type','scheduled_time','scheduled_end_time','defense_venue'] as $f)$t->string($f)->nullable();$t->date('scheduled_date')->nullable();$t->json('workflow_history')->nullable();$t->timestamps();});
    (require glob(database_path('migrations/*create_notifications_table.php'))[0])->up();
    (require database_path('migrations/2026_10_23_000003_add_notification_event_tracking.php'))->up();
    foreach(['2026_10_10_000001_create_finance_honorarium_submissions_table.php','2026_10_17_000001_create_finance_document_submissions_table.php','2026_10_17_000002_create_honorarium_activities_table.php'] as $m)(require database_path('migrations/'.$m))->up();
    foreach(['Student','Administrative Assistant','Finance','Adviser','Coordinator','Adviser'] as $i=>$role) DB::table('users')->insert(['id'=>$i+1,'role'=>$role,'first_name'=>'Demo','last_name'=>'Person','email'=>'user'.($i+1).'@example.test','school_id'=>'S'.($i+1),'program'=>'Test','email_verified_at'=>now()]);
    DB::table('panelists')->insert(['id'=>1,'name'=>'Demo Person','email'=>'user4@example.test']);
    Mail::fake();Http::preventStrayRequests();
    $this->travelTo(\Carbon\Carbon::parse('2026-10-24 08:00:00','Asia/Manila'));
});
afterEach(function(){
    $this->travelBack();foreach(['honorarium_activities','finance_document_submissions','finance_honorarium_submissions','notifications','honorarium_payments','defense_requests','payment_rates','panelists','advisers','users'] as $t)Schema::dropIfExists($t);
});
function integratedDefense(array $data=[]): DefenseRequest{return DefenseRequest::create($data+['submitted_by'=>1,'adviser_user_id'=>4,'coordinator_user_id'=>5,'thesis_title'=>'Integration Study','program'=>'Test','workflow_state'=>'coordinator-approved','status'=>'Approved','proof_of_payment'=>'defense_requirements/receipt.pdf','defense_chairperson'=>1]);}
function sendIntegratedReceipt(DefenseRequest $d){test()->actingAs(User::find(2))->post('/honorarium-documents/'.$d->id.'/submit-one')->assertRedirect();return FinanceDocumentSubmission::where('document_type','payment_receipt')->where('defense_request_id',$d->id)->firstOrFail();}
it('sends receipts once and notifies Finance without sending email',function(){
 $d=integratedDefense();$doc=sendIntegratedReceipt($d);
 expect($doc->payload['receipt_path'])->toBe($d->proof_of_payment)->and(FinanceDocumentSubmission::count())->toBe(2)->and(Notification::where('user_id',3)->count())->toBe(1);
 $this->postJson('/honorarium-documents/'.$d->id.'/submit-one')->assertStatus(422);
 expect(FinanceDocumentSubmission::count())->toBe(2);Mail::assertNothingSent();
});
it('allows only the AA to submit and Finance to review',function(){
 $d=integratedDefense();$this->actingAs(User::find(1))->postJson('/honorarium-documents/'.$d->id.'/submit-one')->assertForbidden();
 $doc=sendIntegratedReceipt($d);$this->postJson('/honorarium-documents/finance/'.$doc->id.'/review',['status'=>'verified'])->assertForbidden();
 $this->actingAs(User::find(3))->post('/honorarium-documents/finance/'.$doc->id.'/review',['status'=>'verified'])->assertRedirect();
 expect($doc->fresh()->status)->toBe('verified')->and($doc->fresh()->reviewed_by)->toBe(3);
});
it('unlocks once on the next Philippine calendar day',function(){
 $d=integratedDefense();sendIntegratedReceipt($d);$service=app(FinanceDocumentWorkflowService::class);
 expect($service->unlockDueDocuments())->toBe(0);$this->travelTo(\Carbon\Carbon::parse('2026-10-25 00:01:00','Asia/Manila'));
 expect($service->unlockDueDocuments())->toBe(1)->and($service->unlockDueDocuments())->toBe(0)->and(HonorariumActivity::where('activity_type','document_ready')->count())->toBe(1);
});
it('requires a verified receipt and current panelists before sending the breakdown',function(){
 $d=integratedDefense();$first=sendIntegratedReceipt($d);$this->travel(1)->days();
 $this->postJson('/honorarium-documents/'.$d->id.'/submit-two')->assertStatus(422);
 $this->actingAs(User::find(3))->post('/honorarium-documents/finance/'.$first->id.'/review',['status'=>'verified'])->assertRedirect();
 $this->actingAs(User::find(2))->post('/honorarium-documents/'.$d->id.'/submit-two')->assertRedirect();
 $second=FinanceDocumentSubmission::where('document_type','panel_breakdown')->first();expect($second->payload['panel_names'])->toBe(['Demo Person']);
 $this->actingAs(User::find(3))->post('/honorarium-documents/finance/'.$second->id.'/review',['status'=>'verified'])->assertRedirect();
 $this->get('/honorarium-workspace')->assertOk()->assertInertia(fn(Assert $p)=>$p->component('honorarium/workspace/Index')->where('items.0.finance_status','verified'));
});
it('requires reasons for rejection and keeps prior review activity on resend',function(){
 $d=integratedDefense();$doc=sendIntegratedReceipt($d);$this->actingAs(User::find(3))->postJson('/honorarium-documents/finance/'.$doc->id.'/review',['status'=>'rejected'])->assertUnprocessable();
 $this->post('/honorarium-documents/finance/'.$doc->id.'/review',['status'=>'rejected','finance_notes'=>'Receipt unreadable'])->assertRedirect();
 sendIntegratedReceipt($d);expect(HonorariumActivity::where('activity_type','finance_review')->count())->toBe(1)->and(FinanceDocumentSubmission::count())->toBe(2);
});
it('does not send unapproved or correction-pending defenses to Finance',function($state){
 $d=integratedDefense(['workflow_state'=>$state]);$this->actingAs(User::find(2))->postJson('/honorarium-documents/'.$d->id.'/submit-one')->assertStatus(422);expect(FinanceDocumentSubmission::count())->toBe(0);
})->with(['submitted','adviser-review','adviser-corrections','coordinator-corrections','cancelled']);
it('scopes adviser records by account email rather than a shared name and protects activity logs',function(){
 $d=integratedDefense();sendIntegratedReceipt($d);
 $this->actingAs(User::find(4))->get('/honorarium-workspace')->assertOk()->assertInertia(fn(Assert $p)=>$p->has('items',1));
 $this->actingAs(User::find(6))->get('/honorarium-documents')->assertOk()->assertInertia(fn(Assert $p)=>$p->has('items',0)->has('activities',0));
 $this->actingAs(User::find(1))->get('/honorarium-workspace')->assertForbidden();
});
it('does not verify a receipt that has been replaced since it was sent',function(){
 $d=integratedDefense();$doc=sendIntegratedReceipt($d);$d->update(['proof_of_payment'=>'new-receipt.pdf']);
 $this->actingAs(User::find(3))->postJson('/honorarium-documents/finance/'.$doc->id.'/review',['status'=>'verified'])->assertStatus(422);
 expect($doc->fresh()->status)->toBe('submitted');
});
it('does not verify obsolete panel names after an assignment changes',function(){
 $d=integratedDefense();$first=sendIntegratedReceipt($d);$this->actingAs(User::find(3))->post('/honorarium-documents/finance/'.$first->id.'/review',['status'=>'verified']);
 $this->travel(1)->days();$this->actingAs(User::find(2))->post('/honorarium-documents/'.$d->id.'/submit-two');
 $second=FinanceDocumentSubmission::where('document_type','panel_breakdown')->first();$d->update(['defense_chairperson'=>null]);
 $this->actingAs(User::find(3))->postJson('/honorarium-documents/finance/'.$second->id.'/review',['status'=>'verified'])->assertStatus(422);
});
it('keeps Finance in its workspace and denies unauthenticated access',function(){
 $this->getJson('/honorarium-documents')->assertUnauthorized();
 $this->actingAs(User::find(3))->get('/dashboard')->assertRedirect('/honorarium-workspace');
});


