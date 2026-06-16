/*
 * ea-sequence-build.js  --  Sparx Enterprise Architect 15.2, JScript (WSH/ES3)
 *
 * Builds UML Sequence diagrams from a data model. ES3 ONLY: var, classic for
 * loops, string concat. No let/const/arrow/forEach/template-literals.
 *
 * Constants below were MEASURED off a hand-drawn EA 15.2 sequence diagram via
 * ea-seq-probe.js + ea-seq-probe2.js, not guessed:
 *   Lifeline           = Element type "Sequence"  (MetaType "Sequence")
 *   Message            = Connector type "Sequence"; ORDER = connector.SequenceNo
 *                        (1,2,3 top->bottom). Sync call is the AddNew default.
 *   Activation bar     = auto-rendered by EA for sync calls (engine adds none).
 *   Combined fragment  = Element type "InteractionFragment"
 *      operator (alt)  -> t_object.PDATA1 = "1"   (set via Repository.Execute)
 *      operands+guards -> t_xref row Name="Partitions", Description blob:
 *          @PAR;Name=<guard>;Size=<regionPx>;GUID={..};@ENDPAR;  (one per region)
 *   Geometry           = X positive; pass POSITIVE t/b with t<b, EA negates Y.
 *
 * MESSAGE Y IS NOT SET BY US. EA auto-stacks messages by SequenceNo at its own
 * vertical pitch. The fragment box (t_object L/R/T/B) IS absolute pixels, so it
 * must be placed to ENCLOSE the auto-laid messages. We PREDICT message Y as
 *      yOf(seq) = MSG_ORIGIN + seq * MSG_PITCH
 * and place the box from that. MSG_ORIGIN / MSG_PITCH are CALIBRATION CONSTANTS:
 * the first UC-16 render prints the Y this engine assumed for every message;
 * compare to the actual render, adjust the two constants, re-run. Expect 2-3
 * iterations on the box only -- that is the gate working, not failing.
 *
 * RUN
 *   1. Project Browser: single-click the PACKAGE that should receive the
 *      diagram (e.g. a package named "Diagram Sekuens").
 *   2. Specialize > Tools > Scripting > new JScript > paste this file > Run.
 *   3. Read the "ASSUMED MESSAGE Y" + "SANITY" output; eyeball the box.
 */

/* ===================== layout / calibration constants ===================== */

var LIFE_X0    = 60;     // left x of first lifeline head
var LIFE_STEP  = 200;    // horizontal gap between lifeline heads (real names are wide)
var LIFE_W     = 110;    // lifeline head box width
var LIFE_TOP   = 30;     // y of lifeline head top
var LIFE_FOOT_PAD = 60;  // extra lifeline length below the last message

// MEASURED message stacking. EA stores NO message Y (probe3: Geometry/Path
// empty, t_diagramlinks zero rows) -- it computes position from SequenceNo at
// a FIXED pitch. These two were read off the UC-16 render (probe3 image):
// messages evenly spaced, seq1 y~136 .. seq10 y~505 => pitch (505-136)/9 ~= 41.
// Uniform pitch held WITH activation bars on. WARNING: self-messages (from==to)
// and return/dashed replies are NOT proven at this pitch -- EA draws a self-call
// bracket ~1.5-2x a row. UC-16 has neither; re-verify before trusting on a UC
// that does. Verification = re-render + eyeball (Y is unreadable, no auto-check).
var MSG_ORIGIN = 136;    // y of the FIRST message (seqNo 1)
var MSG_PITCH  = 41;     // vertical gap EA leaves between consecutive messages

// alt fragment box padding around the messages it encloses
var FRAG_PAD_X   = 24;   // box extends this far left/right beyond outermost lifeline
var FRAG_TOP_PAD = 34;   // room above first enclosed msg for "alt" tab + advance() clearance
var FRAG_BOT_PAD = 13;   // room below last enclosed msg (calibrated on UC-16)
// Clearance placed ABOVE each branch's first message (below the divider that
// opens its operand). EA prints the guard label (~GUARD_ROW_H) at the top of
// that gap, so the VISIBLE space before the message is GAP_ABOVE - GUARD_ROW_H.
// Set to FRAG_TOP_PAD (34) so fail()/reserve() get the same label+gap look as
// advance() under the box top. Pitch is fixed ~41, so the upper branch's last
// message keeps only ~MSG_PITCH - GAP_ABOVE (~7px) below it -- intentional: the
// user prioritised first-message margin. Lower toward ~28 if an upper message
// ever touches its divider.
var GAP_ABOVE = 34;
// EA adds a guard-label row to each operand region's height that we do not
// control and that ACCUMULATES down the box (lower dividers drift a full row).
// Subtract this from every region Size to cancel it. Calibrate on UC-16: if the
// last divider still sits too low, raise it; if dividers ride too high, lower.
var GUARD_ROW_H = 18;

/* ============================== primitives ============================== */

// predicted y-center of the message at 1-based sequence number n
function yOf(n) {
    return MSG_ORIGIN + (n - 1) * MSG_PITCH;
}

// x-center of a lifeline given its 0-based index
function lifeCenterX(idx) {
    return LIFE_X0 + (idx * LIFE_STEP) + (LIFE_W / 2);
}

// synth a GUID EA accepts: {8-4-4-4-12} hex, version-4-ish
function newGuid() {
    var hex = "0123456789ABCDEF";
    var s = "";
    var i;
    for (i = 0; i < 32; i++) {
        if (i === 8 || i === 12 || i === 16 || i === 20) { s += "-"; }
        s += hex.charAt(Math.floor(Math.random() * 16));
    }
    return "{" + s + "}";
}

function escSql(v) {
    if (!v) { return ""; }
    return ("" + v).replace(/'/g, "''");
}

function addLifeline(pkg, name) {
    var e = pkg.Elements.AddNew(name, "Sequence");
    e.Update();
    return e;
}

// place a diagram object; l/t positive, EA stores negated internally
function place(dia, el, l, t, w, h) {
    var r = l + w;
    var b = t + h;
    var geo = "l=" + l + ";r=" + r + ";t=" + t + ";b=" + b + ";";
    var dobj = dia.DiagramObjects.AddNew(geo, "");
    dobj.ElementID = el.ElementID;
    dobj.Update();
    return dobj;
}

// create a sequence message; order is the connector's SequenceNo
function addMessage(srcEl, tgtEl, name, seqNo) {
    var c = srcEl.Connectors.AddNew(name, "Sequence");
    c.SupplierID = tgtEl.ElementID;
    c.SequenceNo = seqNo;
    c.Update();
    srcEl.Connectors.Refresh();
    return c;
}

/* ====================== combined-fragment (alt) writer ===================== */
/*
 * After the InteractionFragment element exists, two raw-table writes finish it:
 *   1. operator: t_object.PDATA1 = '1'  (1 = alt)
 *   2. operands: one t_xref "Partitions" row whose Description is a blob of
 *      @PAR;Name=<guard>;Size=<px>;GUID={..};@ENDPAR; segments (top region first).
 * operands = [{ guard: "lulus", size: 90 }, ...]
 */
function configureAlt(repo, fragEl, operands) {
    var oid = fragEl.ElementID;
    repo.Execute("UPDATE t_object SET PDATA1='1' WHERE Object_ID=" + oid);

    var desc = "";
    var i;
    for (i = 0; i < operands.length; i++) {
        desc += "@PAR;Name=" + operands[i].guard +
                ";Size=" + operands[i].size +
                ";GUID=" + newGuid() + ";@ENDPAR;";
    }
    var xref = newGuid();
    var guid = fragEl.ElementGUID; // already brace-wrapped
    repo.Execute(
        "INSERT INTO t_xref (XrefID, Name, Type, Behavior, Client, Description) VALUES (" +
        "'" + xref + "', 'Partitions', 'element property', '', '" + guid + "', '" +
        escSql(desc) + "')"
    );
}

/* =========================== sequence sanity-check ========================= */
/*
 * No screenshot needed. Verifies the three invariants we committed to:
 *   1. SequenceNo strictly increasing top->bottom (no dup / gap that reorders).
 *   2. Lifeline X centers strictly increasing & non-overlapping.
 *   3. Each alt box Y-range ENCLOSES every message in its operands
 *      (boxTop < yOf(firstMsg) and boxBottom > yOf(lastMsg)).
 * Returns the count of real issues.
 */
function sanityCheck(model, boxes) {
    Session.Output("---- SANITY ----");
    var issues = 0;
    var i;

    // 1. SequenceNo monotonic
    var prev = 0;
    for (i = 0; i < model.messages.length; i++) {
        var sn = model.messages[i].seq;
        if (sn <= prev) {
            Session.Output("  ORDER: message [" + i + "] seq=" + sn + " not > prev " + prev);
            issues++;
        }
        prev = sn;
    }

    // 2. lifeline X non-overlap
    for (i = 1; i < model.lifelines.length; i++) {
        var xPrev = lifeCenterX(i - 1);
        var xCur = lifeCenterX(i);
        if (xCur - xPrev < LIFE_W) {
            Session.Output("  OVERLAP: lifelines " + (i - 1) + "/" + i +
                " centers " + xPrev + "/" + xCur + " closer than LIFE_W " + LIFE_W);
            issues++;
        }
    }

    // 3. box encloses its messages
    var b;
    for (b = 0; b < boxes.length; b++) {
        var bx = boxes[b];
        var topY = yOf(bx.firstSeq);
        var botY = yOf(bx.lastSeq);
        if (!(bx.t < topY)) {
            Session.Output("  ENCLOSE: box '" + bx.label + "' top " + bx.t +
                " not above first msg y " + topY);
            issues++;
        }
        if (!(bx.b > botY)) {
            Session.Output("  ENCLOSE: box '" + bx.label + "' bottom " + bx.b +
                " not below last msg y " + botY);
            issues++;
        }
    }

    if (issues === 0) { Session.Output("  CLEAN: order/overlap/enclosure all OK."); }
    Session.Output("---- " + issues + " issue(s) ----");
    return issues;
}

/* ============================== render engine ============================= */
/*
 * model = {
 *   ucId, title,
 *   lifelines: [ "HR Admin", "CvScreeningController", ... ],   // index = column
 *   messages:  [ { from: idx, to: idx, name: "decide()", seq: 1 }, ... ],
 *   alt: { firstSeq, lastSeq, leftIdx, rightIdx,
 *          operands: [ { guard, firstSeq, lastSeq }, ... ] }    // optional
 * }
 */
function renderUC(repo, pkg, model) {
    var dia = pkg.Diagrams.AddNew(model.ucId + " " + model.title, "Sequence");
    dia.Update();
    pkg.Diagrams.Refresh();

    // 1. lifelines across the top
    var maxSeq = 0, i;
    for (i = 0; i < model.messages.length; i++) {
        if (model.messages[i].seq > maxSeq) { maxSeq = model.messages[i].seq; }
    }
    var footY = yOf(maxSeq) + LIFE_FOOT_PAD;
    var lifeEls = [];
    for (i = 0; i < model.lifelines.length; i++) {
        var le = addLifeline(pkg, model.lifelines[i]);
        place(dia, le, LIFE_X0 + (i * LIFE_STEP), LIFE_TOP, LIFE_W, footY - LIFE_TOP);
        lifeEls.push(le);
    }

    // 2. messages, ordered by SequenceNo
    for (i = 0; i < model.messages.length; i++) {
        var m = model.messages[i];
        addMessage(lifeEls[m.from], lifeEls[m.to], m.name, m.seq);
    }

    // 3. alt fragment (optional)
    var boxes = [];
    if (model.alt) {
        var a = model.alt;
        // Per-model overrides (default to the UC-16-calibrated globals so the
        // 3-op pipeline UCs 16/19/21 are NOT affected). 2-op UCs whose regions
        // hold only 1-2 messages look cramped at the bottom under the locked
        // 41px pitch -- they set a smaller gapAbove (pushes the inter-branch
        // divider DOWN, giving the upper region's last message more space below)
        // and a larger botPad (more room under the last message before the box
        // floor). Trade: the lower region's first message keeps gapAbove-GUARD_ROW_H
        // of visible top space; fine for the short fallback branches.
        var gapAbove = (a.gapAbove != null) ? a.gapAbove : GAP_ABOVE;
        var botPad   = (a.botPad   != null) ? a.botPad   : FRAG_BOT_PAD;
        var topPad   = (a.topPad   != null) ? a.topPad   : FRAG_TOP_PAD;

        var lx = LIFE_X0 + (a.leftIdx * LIFE_STEP) - FRAG_PAD_X;
        var rx = LIFE_X0 + (a.rightIdx * LIFE_STEP) + LIFE_W + FRAG_PAD_X;
        var topY = yOf(a.firstSeq) - topPad;

        // Operand region heights, computed so each inter-branch divider lands
        // GAP_ABOVE px above the LOWER branch's first message (and ~PITCH-GAP
        // below the upper branch's last). Region boundaries are explicit Y's:
        //   region[k] top    = (k==0) ? topY : yOf(branch[k].first) - GAP_ABOVE
        //   region[k] bottom = (k==last) ? yOf(lastSeq)+FRAG_BOT_PAD
        //                                 : yOf(branch[k+1].first) - GAP_ABOVE
        // CRUCIAL: the box bottom is DERIVED from the size sum (botY=topY+sum),
        // not the other way round. EA appears to NORMALIZE operand sizes to the
        // box height; if box height != sum, lower dividers drift (seen as
        // 'reserve' sitting on its divider while 'fail' was fine). Forcing
        // height==sum makes scale exactly 1 so divider_k = topY+cumsum either
        // way -- robust to whether EA normalizes.
        var sizes = [];    // top -> bottom
        var k;
        for (k = 0; k < a.operands.length; k++) {
            var op = a.operands[k];
            var regTop = (k === 0) ? topY : (yOf(op.firstSeq) - gapAbove);
            var regBot;
            if (k < a.operands.length - 1) {
                regBot = yOf(a.operands[k + 1].firstSeq) - gapAbove;
            } else {
                regBot = yOf(op.lastSeq) + botPad;
            }
            // EA adds GUARD_ROW_H of label height to EACH operand region beyond
            // the Size we write, and it ACCUMULATES downward (divider 1 ok,
            // divider 2 drifts a full row). Subtract it per region so EA's added
            // rows restore the intended divider Y. (Tune GUARD_ROW_H if needed.)
            sizes.push({ guard: op.guard, size: Math.round(regBot - regTop) - GUARD_ROW_H });
        }
        var sumH = 0;
        for (k = 0; k < sizes.length; k++) { sumH += sizes[k].size; }
        var botY = topY + sumH;

        var fragEl = pkg.Elements.AddNew("", "InteractionFragment");
        fragEl.Update();
        place(dia, fragEl, lx, topY, rx - lx, sumH);

        // EA stacks the Partitions blob BOTTOM-TO-TOP (probe3: input order
        // lulus/gagal/ditangguhkan rendered top->bottom as ditangguhkan/gagal/
        // lulus). a.operands stays top->bottom for the reader; emit REVERSED.
        var operands = [];
        for (k = 0; k < sizes.length; k++) {
            operands.unshift(sizes[k]);
        }
        configureAlt(repo, fragEl, operands);

        boxes.push({ label: "alt", t: topY, b: botY,
                     firstSeq: a.firstSeq, lastSeq: a.lastSeq });
    }

    // 4. report assumed Y (for calibration) + sanity check
    Session.Output("== " + model.ucId + " " + model.title + " ==");
    Session.Output("ASSUMED MESSAGE Y (MSG_ORIGIN=" + MSG_ORIGIN + ", MSG_PITCH=" + MSG_PITCH + "):");
    for (i = 0; i < model.messages.length; i++) {
        var mm = model.messages[i];
        Session.Output("  seq " + mm.seq + " y~" + yOf(mm.seq) + "  " +
            model.lifelines[mm.from] + " -> " + model.lifelines[mm.to] + " : " + mm.name);
    }
    var issues = sanityCheck(model, boxes);

    repo.ReloadDiagram(dia.DiagramID);
    repo.OpenDiagram(dia.DiagramID);
    Session.Output("Built " + model.ucId + " (" + model.lifelines.length +
        " lifelines, " + model.messages.length + " messages).");
    return issues;
}

/* ============================== UC-16 data ============================== */
/*
 * Skrining CV HR. Lifelines (real classes, read from CvScreeningController +
 * ApplicationPipelineService + EmailNotificationService):
 *   0 HR Admin (actor)
 *   1 CvScreeningController
 *   2 ApplicationPipelineService
 *   3 ApplicationStage           (Model = entity + DB)
 *   4 EmailNotificationService   (Mailer role)
 *
 * Main success flow then a 3-operand alt on the keputusan (lulus/gagal/reserved).
 * Method names are verbatim from the code.
 */
var UC16 = {
    ucId: "UC-16",
    title: "Skrining CV HR",
    lifelines: [
        "HR Admin",
        "CvScreeningController",
        "ApplicationPipelineService",
        "ApplicationStage",
        "EmailNotificationService"
    ],
    messages: [
        { from: 0, to: 1, name: "decide(request, lowongan, application)", seq: 1 },
        { from: 1, to: 3, name: "update(catatan, reviewed_by)",          seq: 2 },
        // alt [lulus]
        { from: 1, to: 2, name: "advance(application)",                  seq: 3 },
        { from: 2, to: 3, name: "update(status: Selesai/Aktif)",         seq: 4 },
        { from: 2, to: 4, name: "dispatch('transisi_tahap', ...)",       seq: 5 },
        // alt [gagal]
        { from: 1, to: 2, name: "fail(application)",                     seq: 6 },
        { from: 2, to: 3, name: "update(status: Gagal)",                 seq: 7 },
        { from: 2, to: 4, name: "dispatch('kandidat_ditolak', ...)",     seq: 8 },
        // alt [ditangguhkan]
        { from: 1, to: 2, name: "reserve(application)",                  seq: 9 },
        { from: 2, to: 3, name: "update(status: Reserved)",              seq: 10 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 10, leftIdx: 1, rightIdx: 4,
        operands: [
            { guard: "lulus",         firstSeq: 3, lastSeq: 5 },
            { guard: "gagal",         firstSeq: 6, lastSeq: 8 },
            { guard: "ditangguhkan",  firstSeq: 9, lastSeq: 10 }
        ]
    }
};

/* ============================== UC-19 data ============================== */
/*
 * Tinjau Jawaban Tes. TestReviewController.decide -> pipelineService
 * advance/fail/reserve (SAME 3-operand pipeline tail as UC-16). Lifelines read
 * from TestReviewController + ApplicationPipelineService. No self-calls/returns.
 */
var UC19 = {
    ucId: "UC-19",
    title: "Tinjau Jawaban Tes",
    lifelines: [
        "HR Admin",
        "TestReviewController",
        "ApplicationPipelineService",
        "ApplicationStage",
        "EmailNotificationService"
    ],
    messages: [
        { from: 0, to: 1, name: "decide(request, lowongan, submission)", seq: 1 },
        { from: 1, to: 3, name: "update(catatan)",                       seq: 2 },
        // alt [lulus]
        { from: 1, to: 2, name: "advance(application)",                  seq: 3 },
        { from: 2, to: 3, name: "update(status: Selesai/Aktif)",         seq: 4 },
        { from: 2, to: 4, name: "dispatch('transisi_tahap', ...)",       seq: 5 },
        // alt [gagal]
        { from: 1, to: 2, name: "fail(application)",                     seq: 6 },
        { from: 2, to: 3, name: "update(status: Gagal)",                 seq: 7 },
        { from: 2, to: 4, name: "dispatch('kandidat_ditolak', ...)",     seq: 8 },
        // alt [ditangguhkan]
        { from: 1, to: 2, name: "reserve(application)",                  seq: 9 },
        { from: 2, to: 3, name: "update(status: Reserved)",              seq: 10 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 10, leftIdx: 1, rightIdx: 4,
        operands: [
            { guard: "lulus",        firstSeq: 3, lastSeq: 5 },
            { guard: "gagal",        firstSeq: 6, lastSeq: 8 },
            { guard: "ditangguhkan", firstSeq: 9, lastSeq: 10 }
        ]
    }
};

/* ============================== UC-21 data ============================== */
/*
 * Wawancara User. InterviewController.decide records InterviewResult then runs
 * the same advance/fail/reserve pipeline. 6 lifelines (result + stage are
 * distinct entities). The 3-op alt tail mirrors UC-16. No self-calls/returns.
 */
var UC21 = {
    ucId: "UC-21",
    title: "Wawancara User",
    lifelines: [
        "Pewawancara",
        "InterviewController",
        "InterviewResult",
        "ApplicationPipelineService",
        "ApplicationStage",
        "EmailNotificationService"
    ],
    messages: [
        { from: 0, to: 1, name: "decide(request, lowongan, application)", seq: 1 },
        { from: 1, to: 2, name: "create(keputusan, catatan, ratings)",    seq: 2 },
        // alt [lulus]
        { from: 1, to: 3, name: "advance(application)",                   seq: 3 },
        { from: 3, to: 4, name: "update(status: Selesai/Aktif)",          seq: 4 },
        { from: 3, to: 5, name: "dispatch('transisi_tahap', ...)",        seq: 5 },
        // alt [gagal]
        { from: 1, to: 3, name: "fail(application)",                      seq: 6 },
        { from: 3, to: 4, name: "update(status: Gagal)",                  seq: 7 },
        { from: 3, to: 5, name: "dispatch('kandidat_ditolak', ...)",      seq: 8 },
        // alt [ditangguhkan]
        { from: 1, to: 3, name: "reserve(application)",                   seq: 9 },
        { from: 3, to: 4, name: "update(status: Reserved)",               seq: 10 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 10, leftIdx: 1, rightIdx: 5,
        operands: [
            { guard: "lulus",        firstSeq: 3, lastSeq: 5 },
            { guard: "gagal",        firstSeq: 6, lastSeq: 8 },
            { guard: "ditangguhkan", firstSeq: 9, lastSeq: 10 }
        ]
    }
};

/* ============================== UC-26 data ============================== */
/*
 * Kirim Surat Penawaran. OfferingLetterController.send: persist offering ->
 * dispatch email -> 2-op alt on send outcome. gagal branch returns error to the
 * actor (drawn as a Call arrow; engine sets no return-type, so pitch holds).
 * leftIdx=0: the error reply touches the actor lifeline, box spans it.
 */
var UC26 = {
    ucId: "UC-26",
    title: "Kirim Surat Penawaran",
    lifelines: [
        "HR Admin",
        "OfferingLetterController",
        "OfferingLetter",
        "EmailNotificationService"
    ],
    messages: [
        { from: 0, to: 1, name: "send(request, lowongan, application)",           seq: 1 },
        { from: 1, to: 2, name: "updateOrCreate(jabatan, gaji, status: pending)", seq: 2 },
        { from: 1, to: 3, name: "dispatch('surat_penawaran', email, links)",      seq: 3 },
        // alt [email terkirim]
        { from: 1, to: 2, name: "update(sent_at)",                                seq: 4 },
        // alt [gagal kirim]
        { from: 1, to: 0, name: "withErrors('Gagal mengirim email penawaran')",   seq: 5 }
    ],
    alt: {
        firstSeq: 4, lastSeq: 5, leftIdx: 0, rightIdx: 2,
        // topPad raises the box top above update(sent_at) (free space under the
        // alt tab). withErrors has no free top -- update sits one 41px pitch above
        // it -- so its top comes from gapAbove, which trades against update's
        // bottom (sum fixed at 41). gapAbove=34 gives withErrors ~16px visible top
        // at the cost of update's bottom (~7px); update still has its 46px top.
        topPad: 46, gapAbove: 34, botPad: 46,
        operands: [
            { guard: "email terkirim", firstSeq: 4, lastSeq: 4 },
            { guard: "gagal kirim",    firstSeq: 5, lastSeq: 5 }
        ]
    }
};

/* ============================== UC-31 data ============================== */
/*
 * Lamar Lowongan. ApplicationController.store -> ApplicationService.store ->
 * persist (Candidate/Application/stages) -> email. 2-op alt on the unique-email
 * constraint: berhasil (create+dispatch) vs sudah melamar (create throws
 * UniqueConstraintViolationException). No self-calls/returns.
 */
var UC31 = {
    ucId: "UC-31",
    title: "Lamar Lowongan",
    lifelines: [
        "Kandidat",
        "ApplicationController",
        "ApplicationService",
        "Application",
        "EmailNotificationService"
    ],
    messages: [
        { from: 0, to: 1, name: "store(request, vacancy)",                seq: 1 },
        { from: 1, to: 2, name: "store(request, vacancy)",                seq: 2 },
        // alt [berhasil]
        { from: 2, to: 3, name: "create(candidate, application, stages)", seq: 3 },
        { from: 2, to: 4, name: "dispatch('lamaran_diterima', ...)",      seq: 4 },
        // alt [sudah melamar]
        { from: 2, to: 3, name: "create(application) [UniqueConstraint]", seq: 5 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 5, leftIdx: 1, rightIdx: 4,
        // gapAbove raised to give create[UniqueConstraint] (lower branch) more
        // top space; costs dispatch ~6px bottom (still ample). botPad unchanged.
        gapAbove: 30, botPad: 34,
        operands: [
            { guard: "berhasil",      firstSeq: 3, lastSeq: 4 },
            { guard: "sudah melamar", firstSeq: 5, lastSeq: 5 }
        ]
    }
};

/* ============================== UC-33 data ============================== */
/*
 * Kerjakan Tes Kompetensi. TestController.submit -> doSubmit (private; COLLAPSED
 * to direct Controller->Model calls to avoid a self-call that voids the pitch).
 * 2-op alt: belum dikerjakan (lock+persist answers+mark) vs sudah dikerjakan
 * (early redirect, drawn as Call to the actor -> leftIdx=0).
 */
var UC33 = {
    ucId: "UC-33",
    title: "Kerjakan Tes Kompetensi",
    lifelines: [
        "Kandidat",
        "TestController",
        "TestSubmission",
        "TestAnswer"
    ],
    messages: [
        { from: 0, to: 1, name: "submit(request, token)",             seq: 1 },
        { from: 1, to: 2, name: "where(token).firstOrFail()",         seq: 2 },
        // alt [belum dikerjakan]
        { from: 1, to: 2, name: "lockForUpdate().findOrFail(id)",     seq: 3 },
        { from: 1, to: 3, name: "create(answers, skor, is_reviewed)", seq: 4 },
        { from: 1, to: 2, name: "update(submitted_at, total_skor)",   seq: 5 },
        // alt [sudah dikerjakan]
        { from: 1, to: 0, name: "redirect('tes.show', token)",        seq: 6 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 6, leftIdx: 0, rightIdx: 3,
        // gapAbove raised to give redirect (lower branch) more top space; costs
        // update bottom (41-34=7px, still ok). botPad unchanged.
        gapAbove: 34, botPad: 34,
        operands: [
            { guard: "belum dikerjakan", firstSeq: 3, lastSeq: 5 },
            { guard: "sudah dikerjakan", firstSeq: 6, lastSeq: 6 }
        ]
    }
};

/* ============================== UC-17 data ============================== */
/*
 * Skrining CV User. CvScreeningController.decide with the user stage
 * (skrining_cv_user). Structurally IDENTICAL to UC-16 (same controller, same
 * 3-op pipeline tail) -- only the actor differs (Kepala Unit / Karyawan).
 * DEFAULT pads. No self-calls/returns.
 */
var UC17 = {
    ucId: "UC-17",
    title: "Skrining CV User",
    lifelines: [
        "Kepala Unit",
        "CvScreeningController",
        "ApplicationPipelineService",
        "ApplicationStage",
        "EmailNotificationService"
    ],
    messages: [
        { from: 0, to: 1, name: "decide(request, lowongan, application)", seq: 1 },
        { from: 1, to: 3, name: "update(catatan, reviewed_by)",          seq: 2 },
        // alt [lulus]
        { from: 1, to: 2, name: "advance(application)",                  seq: 3 },
        { from: 2, to: 3, name: "update(status: Selesai/Aktif)",         seq: 4 },
        { from: 2, to: 4, name: "dispatch('transisi_tahap', ...)",       seq: 5 },
        // alt [gagal]
        { from: 1, to: 2, name: "fail(application)",                     seq: 6 },
        { from: 2, to: 3, name: "update(status: Gagal)",                 seq: 7 },
        { from: 2, to: 4, name: "dispatch('kandidat_ditolak', ...)",     seq: 8 },
        // alt [ditangguhkan]
        { from: 1, to: 2, name: "reserve(application)",                  seq: 9 },
        { from: 2, to: 3, name: "update(status: Reserved)",              seq: 10 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 10, leftIdx: 1, rightIdx: 4,
        operands: [
            { guard: "lulus",        firstSeq: 3, lastSeq: 5 },
            { guard: "gagal",        firstSeq: 6, lastSeq: 8 },
            { guard: "ditangguhkan", firstSeq: 9, lastSeq: 10 }
        ]
    }
};

/* ============================== UC-22 data ============================== */
/*
 * Wawancara Manajer HR. InterviewController.decide with stage
 * wawancara_manajer_hr. Same shape as UC-21 (records InterviewResult then the
 * advance/fail/reserve pipeline). Only the actor differs. DEFAULT pads.
 */
var UC22 = {
    ucId: "UC-22",
    title: "Wawancara Manajer HR",
    lifelines: [
        "Manajer HR",
        "InterviewController",
        "InterviewResult",
        "ApplicationPipelineService",
        "ApplicationStage",
        "EmailNotificationService"
    ],
    messages: [
        { from: 0, to: 1, name: "decide(request, lowongan, application)", seq: 1 },
        { from: 1, to: 2, name: "create(keputusan, catatan, ratings)",    seq: 2 },
        // alt [lulus]
        { from: 1, to: 3, name: "advance(application)",                   seq: 3 },
        { from: 3, to: 4, name: "update(status: Selesai/Aktif)",          seq: 4 },
        { from: 3, to: 5, name: "dispatch('transisi_tahap', ...)",        seq: 5 },
        // alt [gagal]
        { from: 1, to: 3, name: "fail(application)",                      seq: 6 },
        { from: 3, to: 4, name: "update(status: Gagal)",                  seq: 7 },
        { from: 3, to: 5, name: "dispatch('kandidat_ditolak', ...)",      seq: 8 },
        // alt [ditangguhkan]
        { from: 1, to: 3, name: "reserve(application)",                   seq: 9 },
        { from: 3, to: 4, name: "update(status: Reserved)",               seq: 10 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 10, leftIdx: 1, rightIdx: 5,
        operands: [
            { guard: "lulus",        firstSeq: 3, lastSeq: 5 },
            { guard: "gagal",        firstSeq: 6, lastSeq: 8 },
            { guard: "ditangguhkan", firstSeq: 9, lastSeq: 10 }
        ]
    }
};

/* ============================== UC-23 data ============================== */
/*
 * Wawancara Direktur. InterviewController.decide with stage wawancara_direktur.
 * Clone of UC-22 with the Direktur actor. DEFAULT pads.
 */
var UC23 = {
    ucId: "UC-23",
    title: "Wawancara Direktur",
    lifelines: [
        "Direktur",
        "InterviewController",
        "InterviewResult",
        "ApplicationPipelineService",
        "ApplicationStage",
        "EmailNotificationService"
    ],
    messages: [
        { from: 0, to: 1, name: "decide(request, lowongan, application)", seq: 1 },
        { from: 1, to: 2, name: "create(keputusan, catatan, ratings)",    seq: 2 },
        // alt [lulus]
        { from: 1, to: 3, name: "advance(application)",                   seq: 3 },
        { from: 3, to: 4, name: "update(status: Selesai/Aktif)",          seq: 4 },
        { from: 3, to: 5, name: "dispatch('transisi_tahap', ...)",        seq: 5 },
        // alt [gagal]
        { from: 1, to: 3, name: "fail(application)",                      seq: 6 },
        { from: 3, to: 4, name: "update(status: Gagal)",                  seq: 7 },
        { from: 3, to: 5, name: "dispatch('kandidat_ditolak', ...)",      seq: 8 },
        // alt [ditangguhkan]
        { from: 1, to: 3, name: "reserve(application)",                   seq: 9 },
        { from: 3, to: 4, name: "update(status: Reserved)",               seq: 10 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 10, leftIdx: 1, rightIdx: 5,
        operands: [
            { guard: "lulus",        firstSeq: 3, lastSeq: 5 },
            { guard: "gagal",        firstSeq: 6, lastSeq: 8 },
            { guard: "ditangguhkan", firstSeq: 9, lastSeq: 10 }
        ]
    }
};

/* ============================== UC-25 data ============================== */
/*
 * Keputusan MCU. McuController.store records an McuResult then runs the
 * pipeline. ASYMMETRIC 3-op: MCU lulus advances to onboarding, which is a
 * SILENT stage (no email) -> lulus branch = advance + update only, NO dispatch.
 * tidak lulus = fail + update(Gagal) + dispatch('kandidat_ditolak').
 * ditangguhkan = reserve + update(Reserved). DEFAULT pads (operand row counts
 * 2/3/2 mirror UC-16's tolerances). No self-calls/returns.
 */
var UC25 = {
    ucId: "UC-25",
    title: "Keputusan MCU",
    lifelines: [
        "HR Admin",
        "McuController",
        "McuResult",
        "ApplicationPipelineService",
        "ApplicationStage",
        "EmailNotificationService"
    ],
    messages: [
        { from: 0, to: 1, name: "store(request, lowongan, application)",  seq: 1 },
        { from: 1, to: 2, name: "create(keputusan, dokumen_path, catatan)", seq: 2 },
        // alt [lulus]
        { from: 1, to: 3, name: "advance(application)",                   seq: 3 },
        { from: 3, to: 4, name: "update(status: Selesai/Aktif)",          seq: 4 },
        // alt [tidak lulus]
        { from: 1, to: 3, name: "fail(application)",                      seq: 5 },
        { from: 3, to: 4, name: "update(status: Gagal)",                  seq: 6 },
        { from: 3, to: 5, name: "dispatch('kandidat_ditolak', ...)",      seq: 7 },
        // alt [ditangguhkan]
        { from: 1, to: 3, name: "reserve(application)",                   seq: 8 },
        { from: 3, to: 4, name: "update(status: Reserved)",               seq: 9 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 9, leftIdx: 1, rightIdx: 5,
        operands: [
            { guard: "lulus",        firstSeq: 3, lastSeq: 4 },
            { guard: "tidak lulus",  firstSeq: 5, lastSeq: 7 },
            { guard: "ditangguhkan", firstSeq: 8, lastSeq: 9 }
        ]
    }
};

/* ============================== UC-36 data ============================== */
/*
 * Terima/Tolak Penawaran. OfferingResponseController accept()/reject() -- the
 * candidate responds to the offer via a signed link. Modelled as a 2-op alt on
 * the response. terima: update(Accepted) + advance + notify HR. tolak:
 * update(Rejected) + offering stage update(Gagal) + notify HR. Both branches
 * hold 3 messages (symmetric) so DEFAULT pads sit evenly like UC-16. The
 * already-responded guard path is omitted (main success flow only).
 */
var UC36 = {
    ucId: "UC-36",
    title: "Terima/Tolak Penawaran",
    lifelines: [
        "Kandidat",
        "OfferingResponseController",
        "OfferingLetter",
        "ApplicationPipelineService",
        "ApplicationStage",
        "Notification"
    ],
    messages: [
        { from: 0, to: 1, name: "accept(offering) / reject(offering)",    seq: 1 },
        // alt [terima]
        { from: 1, to: 2, name: "update(status: Accepted, responded_at)", seq: 2 },
        { from: 1, to: 3, name: "advance(application)",                   seq: 3 },
        { from: 1, to: 5, name: "send(hrAdmins, PenawaranDirespon)",      seq: 4 },
        // alt [tolak]
        { from: 1, to: 2, name: "update(status: Rejected, rejection_reason)", seq: 5 },
        { from: 1, to: 4, name: "update(status: Gagal)",                  seq: 6 },
        { from: 1, to: 5, name: "send(hrAdmins, PenawaranDirespon)",      seq: 7 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 7, leftIdx: 1, rightIdx: 5,
        operands: [
            { guard: "terima", firstSeq: 2, lastSeq: 4 },
            { guard: "tolak",  firstSeq: 5, lastSeq: 7 }
        ]
    }
};

/* ============================== UC-24 data ============================== */
/*
 * Jadwalkan MCU. McuScheduleController.store: set the MCU stage schedule then
 * email the candidate. Linear success; the guard fallback (already scheduled /
 * no active MCU) is the second operand. 2-op alt, lower branch 1 msg -> reuse
 * UC-31's pads (gapAbove 30 pushes the divider down so dispatch keeps bottom
 * room; withErrors gets gapAbove-GUARD_ROW_H top). No self-calls/returns.
 */
var UC24 = {
    ucId: "UC-24",
    title: "Jadwalkan MCU",
    lifelines: [
        "HR Admin",
        "McuScheduleController",
        "ApplicationStage",
        "EmailNotificationService"
    ],
    messages: [
        { from: 0, to: 1, name: "store(request, lowongan, application)", seq: 1 },
        // alt [belum dijadwalkan]
        { from: 1, to: 2, name: "update(jadwal, lokasi)",               seq: 2 },
        { from: 1, to: 3, name: "dispatch('instruksi_mcu', ...)",       seq: 3 },
        // alt [sudah dijadwalkan]
        { from: 1, to: 0, name: "withErrors('MCU sudah dijadwalkan')",  seq: 4 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 4, leftIdx: 0, rightIdx: 3,
        // store(seq1) is PRE-alt: its bottom = box-top gap = 41-topPad, traded
        // against update-top (topPad-GUARD_ROW_H). Lower topPad to give store
        // more bottom. dispatch(seq3) bottom = 41-gapAbove, traded against
        // withErrors-top; lower gapAbove to give dispatch more bottom. Both
        // upper-msg bottoms preferred over the (comfortable) lower-msg tops.
        topPad: 26, gapAbove: 22, botPad: 34,
        operands: [
            { guard: "belum dijadwalkan", firstSeq: 2, lastSeq: 3 },
            { guard: "sudah dijadwalkan", firstSeq: 4, lastSeq: 4 }
        ]
    }
};

/* ============================== UC-32 data ============================== */
/*
 * Isi Data Pribadi. ValidateApplicationStepController.__invoke: per-step AJAX
 * validation, persists nothing. Builds a Validator from StoreApplicationRequest
 * rules; step 1 also checks whether the email already applied. 2-op alt on the
 * validation outcome, BOTH operands 1 msg -> reuse UC-26's pads (topPad 46 free
 * above json(ok); botPad 46 free below json(errors); gapAbove 34 splits the
 * pitch between them). No self-calls/returns.
 */
var UC32 = {
    ucId: "UC-32",
    title: "Isi Data Pribadi",
    lifelines: [
        "Kandidat",
        "ValidateApplicationStepController",
        "StoreApplicationRequest",
        "Validator",
        "Application"
    ],
    messages: [
        { from: 0, to: 1, name: "validate(request, vacancy)",            seq: 1 },
        { from: 1, to: 2, name: "rulesForStep(step)",                    seq: 2 },
        { from: 1, to: 3, name: "make(data, rules)",                     seq: 3 },
        { from: 1, to: 4, name: "where(vacancy).whereHas(email).exists()", seq: 4 },
        // alt [valid]
        { from: 1, to: 0, name: "json(['ok' => true])",                  seq: 5 },
        // alt [tidak valid]
        { from: 1, to: 0, name: "json(['errors'], 422)",                 seq: 6 }
    ],
    alt: {
        firstSeq: 5, lastSeq: 6, leftIdx: 0, rightIdx: 1,
        topPad: 46, gapAbove: 34, botPad: 46,
        operands: [
            { guard: "valid",       firstSeq: 5, lastSeq: 5 },
            { guard: "tidak valid", firstSeq: 6, lastSeq: 6 }
        ]
    }
};

/* ============================== UC-34 data ============================== */
/*
 * Kerjakan Tes DiSC. DiscTestController.submit -> doSubmit (private; COLLAPSED
 * to direct Controller->Model calls to dodge the self-call that voids pitch,
 * same as UC-33). DiSC auto-scores and advances (UC-33 competency did neither):
 * after persisting answers it calls scoringService.calculate then
 * pipelineService.advance. 2-op alt: belum dikerjakan (lock+persist+score+
 * advance, 5 msgs) vs sudah dikerjakan (early redirect, 1 msg, leftIdx 0).
 * Reuse UC-33's pads (gapAbove 34, botPad 34).
 */
var UC34 = {
    ucId: "UC-34",
    title: "Kerjakan Tes DiSC",
    lifelines: [
        "Kandidat",
        "DiscTestController",
        "DiscSubmission",
        "DiscAnswer",
        "DiscScoringService",
        "ApplicationPipelineService"
    ],
    messages: [
        { from: 0, to: 1, name: "submit(request, token)",            seq: 1 },
        { from: 1, to: 2, name: "where(token).firstOrFail()",        seq: 2 },
        // alt [belum dikerjakan]
        { from: 1, to: 2, name: "lockForUpdate().findOrFail(id)",    seq: 3 },
        { from: 1, to: 3, name: "create(most, least)",               seq: 4 },
        { from: 1, to: 2, name: "update(submitted_at)",              seq: 5 },
        { from: 1, to: 4, name: "calculate(submission)",             seq: 6 },
        { from: 1, to: 5, name: "advance(application)",              seq: 7 },
        // alt [sudah dikerjakan]
        { from: 1, to: 0, name: "redirect('tes-disc.show', token)",  seq: 8 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 8, leftIdx: 0, rightIdx: 5,
        // 1-msg "sudah dikerjakan" stays the BOTTOM operand (top-operand reorder
        // REJECTED: small top op collides the two guard labels at the box top).
        // redirect_top = gapAbove - GUARD_ROW_H is PITCH-INDEPENDENT, so it keeps
        // growing with gapAbove; the only cap is advance(seq7) staying above the
        // divider (advance_bottom = realPitch - gapAbove). The 41px "wall" was a
        // calibrated average, never verified -- user's silence on advance proved
        // headroom. Diagnostic: gapAbove 46. If advance still clears the divider,
        // realPitch > 41 and this is fine; else back off to the largest clearing.
        gapAbove: 46, botPad: 34,
        operands: [
            { guard: "belum dikerjakan", firstSeq: 3, lastSeq: 7 },
            { guard: "sudah dikerjakan", firstSeq: 8, lastSeq: 8 }
        ]
    }
};

/* ============================== UC-35 data ============================== */
/*
 * Kerjakan Tes MBTI. MbtiTestController.submit -> doSubmit (COLLAPSED, same
 * self-call dodge as UC-33/34). Identical shape to UC-34: persist answers,
 * scoringService.calculate, pipelineService.advance, or early redirect if
 * already submitted. Reuse UC-33's pads (gapAbove 34, botPad 34).
 */
var UC35 = {
    ucId: "UC-35",
    title: "Kerjakan Tes MBTI",
    lifelines: [
        "Kandidat",
        "MbtiTestController",
        "MbtiSubmission",
        "MbtiAnswer",
        "MbtiScoringService",
        "ApplicationPipelineService"
    ],
    messages: [
        { from: 0, to: 1, name: "submit(request, token)",            seq: 1 },
        { from: 1, to: 2, name: "where(token).firstOrFail()",        seq: 2 },
        // alt [belum dikerjakan]
        { from: 1, to: 2, name: "lockForUpdate().findOrFail(id)",    seq: 3 },
        { from: 1, to: 3, name: "create(jawaban)",                   seq: 4 },
        { from: 1, to: 2, name: "update(submitted_at)",              seq: 5 },
        { from: 1, to: 4, name: "calculate(submission)",             seq: 6 },
        { from: 1, to: 5, name: "advance(application)",              seq: 7 },
        // alt [sudah dikerjakan]
        { from: 1, to: 0, name: "redirect('tes-mbti.show', token)",  seq: 8 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 8, leftIdx: 0, rightIdx: 5,
        // 1-msg "sudah dikerjakan" stays the BOTTOM operand (top-operand reorder
        // REJECTED: small top op collides the two guard labels at the box top).
        // redirect_top = gapAbove - GUARD_ROW_H is PITCH-INDEPENDENT, so it keeps
        // growing with gapAbove; the only cap is advance(seq7) staying above the
        // divider (advance_bottom = realPitch - gapAbove). The 41px "wall" was a
        // calibrated average, never verified -- user's silence on advance proved
        // headroom. Diagnostic: gapAbove 46. If advance still clears the divider,
        // realPitch > 41 and this is fine; else back off to the largest clearing.
        gapAbove: 46, botPad: 34,
        operands: [
            { guard: "belum dikerjakan", firstSeq: 3, lastSeq: 7 },
            { guard: "sudah dikerjakan", firstSeq: 8, lastSeq: 8 }
        ]
    }
};

/* ============================== UC-05 data ============================== */
/*
 * Kelola Unit (CRUD). UnitController.store: FormRequest validates, persist, then
 * redirect. The defining decision is the validation outcome -> 2-op alt
 * [valid]/[tidak valid]. seq1 store() is PRE-alt (above the box, like UC-24), so
 * topPad is lowered to 26 to keep it off the box top. The [tidak valid] branch is
 * the FormRequest's own redirect()->back()->withErrors() (StoreUnitRequest ->
 * actor), drawn as a Call so pitch holds. No self-calls. Shared CRUD template for
 * UC-05..11 (narrative line 164). Pads: topPad 26, gapAbove 34, botPad 34.
 */
var UC05 = {
    ucId: "UC-05",
    title: "Kelola Unit",
    lifelines: [
        "HR Admin",
        "UnitController",
        "StoreUnitRequest",
        "Unit"
    ],
    messages: [
        { from: 0, to: 1, name: "store(request)",                       seq: 1 },
        // alt [valid]
        { from: 1, to: 2, name: "validated()",                          seq: 2 },
        { from: 1, to: 3, name: "create(validated)",                    seq: 3 },
        { from: 1, to: 0, name: "redirect('unit.index').with(status)",  seq: 4 },
        // alt [tidak valid]
        { from: 2, to: 0, name: "redirect()->back()->withErrors()",     seq: 5 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 5, leftIdx: 0, rightIdx: 3,
        topPad: 26, gapAbove: 34, botPad: 34,
        operands: [
            { guard: "valid",       firstSeq: 2, lastSeq: 4 },
            { guard: "tidak valid", firstSeq: 5, lastSeq: 5 }
        ]
    }
};

/* ============================== UC-06 data ============================== */
/*
 * Kelola Akun (CRUD). AccountController.store: find the employee, create the User
 * account, link it back onto the employee, redirect. 5 lifelines (Employee + User
 * are distinct). Same validation 2-op alt as UC-05. valid branch holds 5 msgs;
 * branch height does NOT change the lower-branch divider math (seesaw is pitch-
 * independent), so the shared CRUD pads still apply. No self-calls.
 */
var UC06 = {
    ucId: "UC-06",
    title: "Kelola Akun",
    lifelines: [
        "HR Admin",
        "AccountController",
        "StoreAccountRequest",
        "Employee",
        "User"
    ],
    messages: [
        { from: 0, to: 1, name: "store(request)",                          seq: 1 },
        // alt [valid]
        { from: 1, to: 2, name: "validated()",                             seq: 2 },
        { from: 1, to: 3, name: "findOrFail(employee_id)",                 seq: 3 },
        { from: 1, to: 4, name: "create(username, role, password)",        seq: 4 },
        { from: 1, to: 3, name: "update(user_id)",                         seq: 5 },
        { from: 1, to: 0, name: "redirect('akun.index').with(status)",     seq: 6 },
        // alt [tidak valid]
        { from: 2, to: 0, name: "redirect()->back()->withErrors()",        seq: 7 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 7, leftIdx: 0, rightIdx: 4,
        topPad: 26, gapAbove: 34, botPad: 34,
        operands: [
            { guard: "valid",       firstSeq: 2, lastSeq: 6 },
            { guard: "tidak valid", firstSeq: 7, lastSeq: 7 }
        ]
    }
};

/* ============================== UC-07 data ============================== */
/*
 * Kelola Karyawan (CRUD). EmployeeController.store: FormRequest validates,
 * Employee::create, redirect. Structurally identical to UC-05. Shared CRUD pads.
 */
var UC07 = {
    ucId: "UC-07",
    title: "Kelola Karyawan",
    lifelines: [
        "HR Admin",
        "EmployeeController",
        "StoreEmployeeRequest",
        "Employee"
    ],
    messages: [
        { from: 0, to: 1, name: "store(request)",                          seq: 1 },
        // alt [valid]
        { from: 1, to: 2, name: "validated()",                             seq: 2 },
        { from: 1, to: 3, name: "create(validated)",                       seq: 3 },
        { from: 1, to: 0, name: "redirect('karyawan.index').with(status)", seq: 4 },
        // alt [tidak valid]
        { from: 2, to: 0, name: "redirect()->back()->withErrors()",        seq: 5 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 5, leftIdx: 0, rightIdx: 3,
        topPad: 26, gapAbove: 34, botPad: 34,
        operands: [
            { guard: "valid",       firstSeq: 2, lastSeq: 4 },
            { guard: "tidak valid", firstSeq: 5, lastSeq: 5 }
        ]
    }
};

/* ============================== UC-08 data ============================== */
/*
 * Kelola Template Alur (CRUD). WorkflowTemplateController.store: validate the
 * stage constraints (locked first/last), create the template, sync its stages,
 * redirect. validateStageConstraints/syncStages are private helpers -> routed to
 * model lifelines (Stage / WorkflowTemplate) NOT self, to dodge the pitch-breaking
 * self-call. Same validation 2-op alt. Shared CRUD pads.
 */
var UC08 = {
    ucId: "UC-08",
    title: "Kelola Template Alur",
    lifelines: [
        "HR Admin",
        "WorkflowTemplateController",
        "StoreWorkflowTemplateRequest",
        "WorkflowTemplate",
        "Stage"
    ],
    messages: [
        { from: 0, to: 1, name: "store(request)",                              seq: 1 },
        // alt [valid]
        { from: 1, to: 2, name: "validated()",                                 seq: 2 },
        { from: 1, to: 4, name: "validateStageConstraints(stages)",            seq: 3 },
        { from: 1, to: 3, name: "create(nama)",                                seq: 4 },
        { from: 1, to: 3, name: "syncStages(stageIds)",                        seq: 5 },
        { from: 1, to: 0, name: "redirect('template-alur.index').with(status)", seq: 6 },
        // alt [tidak valid]
        { from: 2, to: 0, name: "redirect()->back()->withErrors()",            seq: 7 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 7, leftIdx: 0, rightIdx: 4,
        topPad: 26, gapAbove: 34, botPad: 34,
        operands: [
            { guard: "valid",       firstSeq: 2, lastSeq: 6 },
            { guard: "tidak valid", firstSeq: 7, lastSeq: 7 }
        ]
    }
};

/* ============================== UC-09 data ============================== */
/*
 * Kelola Template Wawancara (CRUD). InterviewTemplateController.store validates
 * INLINE ($request->validate) -> a "Validator" lifeline (like UC-32) instead of a
 * FormRequest. DB::transaction: create the template then create its items in a
 * loop (drawn as ONE items().create() message per the one-fragment design).
 * Shared CRUD pads.
 */
var UC09 = {
    ucId: "UC-09",
    title: "Kelola Template Wawancara",
    lifelines: [
        "HR Admin",
        "InterviewTemplateController",
        "Validator",
        "InterviewTemplate",
        "InterviewTemplateItem"
    ],
    messages: [
        { from: 0, to: 1, name: "store(request)",                                  seq: 1 },
        // alt [valid]
        { from: 1, to: 2, name: "validate(rules)",                                 seq: 2 },
        { from: 1, to: 3, name: "create(nama, tipe)",                              seq: 3 },
        { from: 1, to: 4, name: "items().create(teks, urutan)",                    seq: 4 },
        { from: 1, to: 0, name: "redirect('template-wawancara.index').with(success)", seq: 5 },
        // alt [tidak valid]
        { from: 2, to: 0, name: "redirect()->back()->withErrors()",                seq: 6 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 6, leftIdx: 0, rightIdx: 4,
        topPad: 26, gapAbove: 34, botPad: 34,
        operands: [
            { guard: "valid",       firstSeq: 2, lastSeq: 5 },
            { guard: "tidak valid", firstSeq: 6, lastSeq: 6 }
        ]
    }
};

/* ============================== UC-10 data ============================== */
/*
 * Kelola Bank Soal (CRUD). QuestionBankTemplateController.store validates INLINE
 * -> Validator lifeline. DB::transaction: create template -> create questions ->
 * create options (nested loops collapsed to one message each per the one-fragment
 * design). 6 lifelines. Shared CRUD pads.
 */
var UC10 = {
    ucId: "UC-10",
    title: "Kelola Bank Soal",
    lifelines: [
        "HR Admin",
        "QuestionBankTemplateController",
        "Validator",
        "QuestionBankTemplate",
        "Question",
        "QuestionOption"
    ],
    messages: [
        { from: 0, to: 1, name: "store(request)",                                   seq: 1 },
        // alt [valid]
        { from: 1, to: 2, name: "validate(rules)",                                  seq: 2 },
        { from: 1, to: 3, name: "create(nama)",                                     seq: 3 },
        { from: 1, to: 4, name: "questions().create(tipe, pertanyaan, nilai_poin)", seq: 4 },
        { from: 1, to: 5, name: "options().create(teks_opsi, is_correct)",          seq: 5 },
        { from: 1, to: 0, name: "redirect('template-bank-soal.index').with(status)", seq: 6 },
        // alt [tidak valid]
        { from: 2, to: 0, name: "redirect()->back()->withErrors()",                 seq: 7 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 7, leftIdx: 0, rightIdx: 5,
        topPad: 26, gapAbove: 34, botPad: 34,
        operands: [
            { guard: "valid",       firstSeq: 2, lastSeq: 6 },
            { guard: "tidak valid", firstSeq: 7, lastSeq: 7 }
        ]
    }
};

/* ============================== UC-11 data ============================== */
/*
 * Kelola Template Email (CRUD, UPDATE-only). EmailTemplateController.update:
 * FormRequest validates, EmailTemplate::update, redirect. No create/destroy. seq1
 * is update() (not store) but the shape is the same validation 2-op alt. Shared
 * CRUD pads.
 */
var UC11 = {
    ucId: "UC-11",
    title: "Kelola Template Email",
    lifelines: [
        "HR Admin",
        "EmailTemplateController",
        "UpdateEmailTemplateRequest",
        "EmailTemplate"
    ],
    messages: [
        { from: 0, to: 1, name: "update(request, templateEmail)",            seq: 1 },
        // alt [valid]
        { from: 1, to: 2, name: "validated()",                               seq: 2 },
        { from: 1, to: 3, name: "update(validated)",                         seq: 3 },
        { from: 1, to: 0, name: "redirect('template-email.index').with(status)", seq: 4 },
        // alt [tidak valid]
        { from: 2, to: 0, name: "redirect()->back()->withErrors()",          seq: 5 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 5, leftIdx: 0, rightIdx: 3,
        topPad: 26, gapAbove: 34, botPad: 34,
        operands: [
            { guard: "valid",       firstSeq: 2, lastSeq: 4 },
            { guard: "tidak valid", firstSeq: 5, lastSeq: 5 }
        ]
    }
};

/* ============================== UC-03 data ============================== */
/*
 * Lihat Notifikasi (display). NotifikasiController.index: paginate the user's
 * notifications, and IF any are unread, mark them read; then render. The
 * `if ($unreadIds->isNotEmpty())` is a REAL controller branch -> 2-op alt whose
 * operands differ by the genuine update(read_at) message (the view is shared but
 * the decision is real). 2 pre-alt msgs (index, paginate) -> topPad 34.
 */
var UC03 = {
    ucId: "UC-03",
    title: "Lihat Notifikasi",
    lifelines: [
        "Pengguna Internal",
        "NotifikasiController",
        "DatabaseNotification"
    ],
    messages: [
        { from: 0, to: 1, name: "index(request)",                                 seq: 1 },
        { from: 1, to: 2, name: "notifications().paginate(15)",                   seq: 2 },
        // alt [ada belum dibaca]
        { from: 1, to: 2, name: "unreadNotifications().whereIn(ids).update(read_at)", seq: 3 },
        { from: 1, to: 0, name: "view('notifikasi.index')",                       seq: 4 },
        // alt [sudah dibaca semua]
        { from: 1, to: 0, name: "view('notifikasi.index')",                       seq: 5 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 5, leftIdx: 0, rightIdx: 2,
        topPad: 34, gapAbove: 34, botPad: 34,
        operands: [
            { guard: "ada belum dibaca",  firstSeq: 3, lastSeq: 4 },
            { guard: "sudah dibaca semua", firstSeq: 5, lastSeq: 5 }
        ]
    }
};

/* ============================== UC-14 data ============================== */
/*
 * Lihat Pipeline (display, LINEAR -- NO alt). VacancyPipelineController.index
 * authorizes, loads the vacancy, queries applications (optional search/stage/
 * status filters), and ALWAYS returns view('pipeline.index'). The "belum ada
 * kandidat" empty state is a Blade @if, NOT a controller branch -- drawing an alt
 * here would be two identical view() operands (fabricated), so it is rendered
 * linear with no combined fragment.
 */
var UC14 = {
    ucId: "UC-14",
    title: "Lihat Pipeline",
    lifelines: [
        "HR Admin",
        "VacancyPipelineController",
        "Vacancy",
        "Application"
    ],
    messages: [
        { from: 0, to: 1, name: "index(request, lowongan)",                       seq: 1 },
        { from: 1, to: 2, name: "load(unit, workflowTemplateSnapshot.stages)",    seq: 2 },
        { from: 1, to: 3, name: "with(candidate, stages).where(vacancy_id).paginate()", seq: 3 },
        { from: 1, to: 0, name: "view('pipeline.index')",                         seq: 4 }
    ]
};

/* ============================== UC-15 data ============================== */
/*
 * Lihat Detail Kandidat (display). VacancyPipelineController.showApplication:
 * authorize, then `abort_if($application->vacancy_id !== $lowongan->id, 404)` --
 * a REAL branch. 2-op alt: [lamaran milik lowongan] eager-loads the full profile
 * graph + renders; [bukan milik lowongan] aborts 404. Single pre-alt msg
 * (showApplication) -> topPad 26 (UC-24 twin). leftIdx 0 (view/abort to actor).
 */
var UC15 = {
    ucId: "UC-15",
    title: "Lihat Detail Kandidat",
    lifelines: [
        "HR Admin",
        "VacancyPipelineController",
        "Application"
    ],
    messages: [
        { from: 0, to: 1, name: "showApplication(lowongan, application)",         seq: 1 },
        // alt [lamaran milik lowongan]
        { from: 1, to: 2, name: "load(candidate.*, stages.*, mcuResult)",         seq: 2 },
        { from: 1, to: 0, name: "view('pipeline.show')",                          seq: 3 },
        // alt [bukan milik lowongan]
        { from: 1, to: 0, name: "abort(404)",                                     seq: 4 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 4, leftIdx: 0, rightIdx: 2,
        // UC-24 twin (single pre-alt, 2-msg top / 1-msg-to-actor bottom). view()
        // is the top operand's last msg; gapAbove pushes the divider DOWN so it
        // clears view's dashed line. gapAbove 34 left it touching; 22 (UC-24's
        // confirmed value) opens it. abort(404) keeps gapAbove-GUARD_ROW_H top.
        topPad: 26, gapAbove: 22, botPad: 34,
        operands: [
            { guard: "lamaran milik lowongan", firstSeq: 2, lastSeq: 3 },
            { guard: "bukan milik lowongan",   firstSeq: 4, lastSeq: 4 }
        ]
    }
};

/* ============================== UC-20 data ============================== */
/*
 * Lihat Hasil DiSC/MBTI (display, LINEAR -- NO alt). Part of the candidate detail
 * page: VacancyPipelineController.showApplication eager-loads discSubmission.result
 * + mbtiSubmission.result and renders. "Tes belum dikerjakan" is a view @if on a
 * null result, NOT a controller branch -- no alt (would be two identical view()
 * operands). Rendered linear.
 */
var UC20 = {
    ucId: "UC-20",
    title: "Lihat Hasil DiSC/MBTI",
    lifelines: [
        "HR Admin",
        "VacancyPipelineController",
        "DiscSubmission",
        "MbtiSubmission"
    ],
    messages: [
        { from: 0, to: 1, name: "showApplication(lowongan, application)",         seq: 1 },
        { from: 1, to: 2, name: "with('discSubmission.result')",                  seq: 2 },
        { from: 1, to: 3, name: "with('mbtiSubmission.result')",                  seq: 3 },
        { from: 1, to: 0, name: "view('pipeline.show')",                          seq: 4 }
    ]
};

/* ============================== UC-30 data ============================== */
/*
 * Lihat Lowongan (display). CareerController.show:
 * `abort_unless($vacancy->status === Published && tenggat->gte(now), 404)` -- a
 * REAL branch. 2-op alt: [tersedia] loads unit/snapshot + renders the public
 * detail; [tidak tersedia] aborts 404. Single pre-alt msg -> topPad 26.
 */
var UC30 = {
    ucId: "UC-30",
    title: "Lihat Lowongan",
    lifelines: [
        "Kandidat",
        "CareerController",
        "Vacancy"
    ],
    messages: [
        { from: 0, to: 1, name: "show(vacancy)",                                  seq: 1 },
        // alt [tersedia]
        { from: 1, to: 2, name: "load(unit, workflowTemplateSnapshot)",           seq: 2 },
        { from: 1, to: 0, name: "view('career.show')",                            seq: 3 },
        // alt [tidak tersedia]
        { from: 1, to: 0, name: "abort(404)",                                     seq: 4 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 4, leftIdx: 0, rightIdx: 2,
        // UC-24 twin: gapAbove 22 pushes the divider clear of view('career.show').
        topPad: 26, gapAbove: 22, botPad: 34,
        operands: [
            { guard: "tersedia",       firstSeq: 2, lastSeq: 3 },
            { guard: "tidak tersedia", firstSeq: 4, lastSeq: 4 }
        ]
    }
};

/* ============================== UC-37 data ============================== */
/*
 * Lihat Status Lamaran (display, tokenized). CandidateStatusController.show:
 * `Application::where('token')->...->firstOrFail()` -- the firstOrFail is a REAL
 * 404 branch. 2-op alt: [token valid] the query returns + renders the status page;
 * [token tidak valid] firstOrFail aborts 404. Single pre-alt msg -> topPad 26.
 */
var UC37 = {
    ucId: "UC-37",
    title: "Lihat Status Lamaran",
    lifelines: [
        "Kandidat",
        "CandidateStatusController",
        "Application"
    ],
    messages: [
        { from: 0, to: 1, name: "show(token)",                                    seq: 1 },
        // alt [token valid]
        { from: 1, to: 2, name: "where(token).with(candidate, vacancy.unit, stages).firstOrFail()", seq: 2 },
        { from: 1, to: 0, name: "view('career.status')",                          seq: 3 },
        // alt [token tidak valid]
        { from: 1, to: 0, name: "abort(404)",                                     seq: 4 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 4, leftIdx: 0, rightIdx: 2,
        // UC-24 twin: gapAbove 22 pushes the divider clear of view('career.status').
        topPad: 26, gapAbove: 22, botPad: 34,
        operands: [
            { guard: "token valid",       firstSeq: 2, lastSeq: 3 },
            { guard: "token tidak valid", firstSeq: 4, lastSeq: 4 }
        ]
    }
};

/* ============================== UC-01 data ============================== */
/*
 * Login (auth). Laravel Fortify: AuthenticatedSessionController.store runs the
 * Fortify::authenticateUsing closure (FortifyServiceProvider) -> User lookup by
 * username, Hash::check, then THREE real outcomes: return the user (valid+aktif),
 * throw ValidationException 'dinonaktifkan' (akun nonaktif), or return null which
 * Fortify turns into the generic 'credentials' error (kredensial salah). The two
 * failure modes (nonaktif, salah) are the SAME interaction -- controller -> actor
 * ValidationException back to login -- differing only in the error string, so they
 * are ONE operand (splitting them is a fabricated fork, same class as UC-14/20, and
 * its render cramps because a 1-msg middle operand is height-locked to the pitch).
 * Login's decision is binary -> 2-op alt. The success branch is SPLIT into two real
 * Fortify post-auth actions (session regenerate, then redirect intended) so the top
 * operand is 2-msg -- a single-msg top operand renders cramped (its one long label
 * overlaps the guard row; the region is height-short). That makes this the standard
 * multi-top / 1-msg-to-actor shape -> 2 pre-alt msgs -> topPad 34. SEESAW: the
 * divider splits redirect (top-last, bottom clearance realPitch-gapAbove) and
 * ValidationException (bottom-first, top clearance gapAbove-GUARD_ROW_H). gapAbove
 * 22 starved ValidationException (4px, collided its guard); gapAbove 34 gives it
 * 16px while redirect keeps realPitch-34 (~13px, realPitch>46 per UC-34) -- both
 * clear. This is the CRUD multi-top/1-bottom value, not the display 22. botPad 34.
 * The is_active rule lives in the guard text
 * (a guard condition, not an interaction). Hash::check folded into the lookup msg;
 * rate limit (login 5/min) is a pre-gate, omitted.
 */
var UC01 = {
    ucId: "UC-01",
    title: "Login",
    lifelines: [
        "Pengguna Internal",
        "AuthenticatedSessionController",
        "User"
    ],
    messages: [
        { from: 0, to: 1, name: "store(LoginRequest)",                         seq: 1 },
        { from: 1, to: 2, name: "where('username').first() + Hash::check",      seq: 2 },
        // alt [kredensial valid & akun aktif]
        { from: 1, to: 0, name: "session()->regenerate()",                     seq: 3 },
        { from: 1, to: 0, name: "redirect()->intended('dashboard')",           seq: 4 },
        // alt [gagal -- akun nonaktif / kredensial salah]
        { from: 1, to: 0, name: "ValidationException(...) -> back()",           seq: 5 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 5, leftIdx: 0, rightIdx: 1,
        topPad: 34, gapAbove: 34, botPad: 34,
        operands: [
            { guard: "kredensial valid & akun aktif",      firstSeq: 3, lastSeq: 4 },
            { guard: "gagal: akun nonaktif / kredensial salah", firstSeq: 5, lastSeq: 5 }
        ]
    }
};

/* ============================== UC-02 data ============================== */
/*
 * Ubah Password (auth). PasswordChangeController.update validates INLINE
 * (current_password + confirmed + Password::min(8)) -> Validator lifeline, then
 * User::update(password, must_change_password:false), redirect. Direct CRUD-update
 * twin (UC-05 shape): single pre-alt update() -> topPad 26 / gapAbove 34 / botPad 34.
 */
var UC02 = {
    ucId: "UC-02",
    title: "Ubah Password",
    lifelines: [
        "Pengguna Internal",
        "PasswordChangeController",
        "Validator",
        "User"
    ],
    messages: [
        { from: 0, to: 1, name: "update(request)",                                 seq: 1 },
        // alt [valid]
        { from: 1, to: 2, name: "validate(current_password, password|confirmed|min:8)", seq: 2 },
        { from: 1, to: 3, name: "update(password, must_change_password: false)",    seq: 3 },
        { from: 1, to: 0, name: "redirect('dashboard').with(status)",              seq: 4 },
        // alt [tidak valid]
        { from: 2, to: 0, name: "redirect()->back()->withErrors()",                seq: 5 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 5, leftIdx: 0, rightIdx: 3,
        topPad: 26, gapAbove: 34, botPad: 34,
        operands: [
            { guard: "valid",       firstSeq: 2, lastSeq: 4 },
            { guard: "tidak valid", firstSeq: 5, lastSeq: 5 }
        ]
    }
};

/* ============================== UC-12 data ============================== */
/*
 * Kelola Lowongan (CRUD + snapshot). VacancyController.store loads the chosen
 * workflow template, then has a REAL guard: publishing a workflow that CONTAINS an
 * unconfigured tes_kompetensi stage is rejected with back()->withErrors. Else it
 * snapshots the template («extend» UC-13) and creates the vacancy. 2-op alt; 2
 * pre-alt msgs (store, findOrFail) -> topPad 34. Guard label matches the withErrors
 * text (publishing blocked until the test is configured).
 */
var UC12 = {
    ucId: "UC-12",
    title: "Kelola Lowongan",
    lifelines: [
        "HR Admin",
        "VacancyController",
        "WorkflowTemplate",
        "WorkflowTemplateSnapshot",
        "Vacancy"
    ],
    messages: [
        { from: 0, to: 1, name: "store(request)",                                  seq: 1 },
        { from: 1, to: 2, name: "with('stages').findOrFail(workflow_template_id)", seq: 2 },
        // alt [draft / boleh publish]
        { from: 1, to: 3, name: "createFromTemplate(template)",                    seq: 3 },
        { from: 1, to: 4, name: "create(data, workflow_template_snapshot_id)",     seq: 4 },
        { from: 1, to: 0, name: "redirect('lowongan.index').with(status)",         seq: 5 },
        // alt [publish, tes belum dikonfigurasi]
        { from: 1, to: 0, name: "back()->withErrors('tes kompetensi belum dikonfigurasi')", seq: 6 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 6, leftIdx: 0, rightIdx: 4,
        topPad: 34, gapAbove: 34, botPad: 34,
        operands: [
            { guard: "draft / boleh publish",        firstSeq: 3, lastSeq: 5 },
            { guard: "publish, tes belum dikonfigurasi", firstSeq: 6, lastSeq: 6 }
        ]
    }
};

/* ============================== UC-13 data ============================== */
/*
 * Terapkan Template Alur (LINEAR, «extend» of UC-12). The snapshot copy detail:
 * VacancyController.store -> WorkflowTemplateSnapshot::createFromTemplate which
 * self::create()s the snapshot then loops the template's stages creating one
 * WorkflowTemplateSnapshotStage each (real body read from the model). No branch --
 * the copy always runs once the template is valid -> no fragment. This UC exists to
 * SHOW that copy, so the snapshot->stage create is modelled explicitly (not opaque).
 */
var UC13 = {
    ucId: "UC-13",
    title: "Terapkan Template Alur",
    lifelines: [
        "HR Admin",
        "VacancyController",
        "WorkflowTemplate",
        "WorkflowTemplateSnapshot",
        "WorkflowTemplateSnapshotStage"
    ],
    messages: [
        { from: 0, to: 1, name: "store(request) [pilih template alur]",           seq: 1 },
        { from: 1, to: 2, name: "with('stages').findOrFail(workflow_template_id)", seq: 2 },
        { from: 1, to: 3, name: "createFromTemplate(template)",                    seq: 3 },
        { from: 3, to: 4, name: "stages().create(position, key, nama, is_locked_*)", seq: 4 },
        { from: 1, to: 0, name: "redirect('lowongan.index').with(status)",         seq: 5 }
    ]
};

/* ============================== UC-18 data ============================== */
/*
 * Kelola Tes Kompetensi (config + snapshot). VacancyTestController.save validates
 * INLINE -> Validator lifeline; firstOrNew + save the VacancyTest, sync its
 * questions, then VacancyTestSnapshot::createFromVacancyTest (shown OPAQUE -- a
 * snapshot factory like UC-25/36's advance, the UC's focus is the config save not
 * the copy internals). 2-op valid/invalid; single pre-alt save() -> topPad 26.
 * Bottom withErrors needs top room (seesaw, same as UC-27): gapAbove 40 -> 22px top,
 * redirect('lowongan.tes.show') keeps realPitch-40 (~7-10px) below. botPad 34.
 */
var UC18 = {
    ucId: "UC-18",
    title: "Kelola Tes Kompetensi",
    lifelines: [
        "HR Admin",
        "VacancyTestController",
        "Validator",
        "VacancyTest",
        "VacancyTestSnapshot"
    ],
    messages: [
        { from: 0, to: 1, name: "save(request, lowongan)",                         seq: 1 },
        // alt [valid]
        { from: 1, to: 2, name: "validate(batas_waktu_menit, question_ids)",       seq: 2 },
        { from: 1, to: 3, name: "firstOrNew().save()",                             seq: 3 },
        { from: 1, to: 3, name: "questions().sync(question_ids)",                  seq: 4 },
        { from: 1, to: 4, name: "createFromVacancyTest(vacancyTest)",              seq: 5 },
        { from: 1, to: 0, name: "redirect('lowongan.tes.show').with(success)",     seq: 6 },
        // alt [tidak valid]
        { from: 2, to: 0, name: "redirect()->back()->withErrors()",                seq: 7 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 7, leftIdx: 0, rightIdx: 4,
        topPad: 26, gapAbove: 40, botPad: 34,
        operands: [
            { guard: "valid",       firstSeq: 2, lastSeq: 6 },
            { guard: "tidak valid", firstSeq: 7, lastSeq: 7 }
        ]
    }
};

/* ============================== UC-27 data ============================== */
/*
 * Proses Onboarding (two-phase, dual-action). OnboardingController.sendInvitation
 * then complete -- TWO HR actions in one flow. sendInvitation (save onboarding +
 * email) is linear pre-alt. complete() has the REAL branch
 * `if (! onboardingStage->status->isAdvanceable()) return back()->withErrors(...)`
 * -> wrapped in a 2-op alt [tahap advanceable: advance + redirect] / [onboarding
 * sudah selesai: withErrors]. (Resolves the deferred dual-action decision: render
 * both phases sequentially, alt only on complete's guard.) seq4 complete is the
 * 2nd actor call, just above the box -> topPad 26. SEESAW (same as UC-01): the lone
 * bottom msg withErrors needs gapAbove-GUARD_ROW_H of top -- 22 gave 4px, 34 gave
 * 16px (still tight). gapAbove 40 -> 22px top; redirect keeps realPitch-40 (~7-10px,
 * realPitch>=47 per UC-34) below it. This is near the seesaw ceiling -- pushing
 * gapAbove higher would starve redirect. gapAbove 40 / botPad 34.
 */
var UC27 = {
    ucId: "UC-27",
    title: "Proses Onboarding",
    lifelines: [
        "HR Admin",
        "OnboardingController",
        "OnboardingResult",
        "EmailNotificationService",
        "ApplicationPipelineService"
    ],
    messages: [
        { from: 0, to: 1, name: "sendInvitation(request, lowongan, application)",  seq: 1 },
        { from: 1, to: 2, name: "updateOrCreate(tanggal_bergabung, catatan, sent_at)", seq: 2 },
        { from: 1, to: 3, name: "dispatch('undangan_onboarding', ...)",           seq: 3 },
        { from: 0, to: 1, name: "complete(lowongan, application)",                 seq: 4 },
        // alt [tahap advanceable]
        { from: 1, to: 4, name: "advance(application)",                            seq: 5 },
        { from: 1, to: 0, name: "redirect('lowongan.pipeline').with(success)",     seq: 6 },
        // alt [onboarding sudah selesai]
        { from: 1, to: 0, name: "back()->withErrors('Tahap onboarding sudah selesai')", seq: 7 }
    ],
    alt: {
        firstSeq: 5, lastSeq: 7, leftIdx: 0, rightIdx: 4,
        topPad: 26, gapAbove: 40, botPad: 34,
        operands: [
            { guard: "tahap advanceable",     firstSeq: 5, lastSeq: 6 },
            { guard: "onboarding sudah selesai", firstSeq: 7, lastSeq: 7 }
        ]
    }
};

/* ============================== UC-29 data ============================== */
/*
 * Tandai Reserved («extend»). The "Ditangguhkan" decision path:
 * ApplicationPipelineService.reserve, called from the stage-decision controllers
 * (CvScreeningController shown as the representative caller). 2-op alt: [ada tahap
 * aktif] reserve -> ApplicationStage update(Reserved); [tidak ada tahap aktif]
 * withErrors. Single pre-alt decide(); 2-msg-top (reserve forward-call + update) /
 * 1-msg-to-actor bottom = UC-24 twin -> topPad 26 / gapAbove 22 / botPad 34.
 */
var UC29 = {
    ucId: "UC-29",
    title: "Tandai Reserved",
    lifelines: [
        "HR Admin",
        "CvScreeningController",
        "ApplicationPipelineService",
        "ApplicationStage"
    ],
    messages: [
        { from: 0, to: 1, name: "decide(keputusan: Ditangguhkan)",                seq: 1 },
        // alt [ada tahap aktif]
        { from: 1, to: 2, name: "reserve(application)",                           seq: 2 },
        { from: 2, to: 3, name: "update(status: Reserved)",                       seq: 3 },
        // alt [tidak ada tahap aktif]
        { from: 1, to: 0, name: "withErrors('Tidak ada tahap aktif yang dapat ditangguhkan')", seq: 4 }
    ],
    alt: {
        firstSeq: 2, lastSeq: 4, leftIdx: 0, rightIdx: 3,
        topPad: 26, gapAbove: 22, botPad: 34,
        operands: [
            { guard: "ada tahap aktif",       firstSeq: 2, lastSeq: 3 },
            { guard: "tidak ada tahap aktif", firstSeq: 4, lastSeq: 4 }
        ]
    }
};

/* ============================== UC-28 data ============================== */
/*
 * Kirim Notifikasi Email (shared «include» utility). EmailNotificationService
 * .dispatch(key, toEmail, payload): look up the EmailTemplate by key, then the
 * real controller branch `if (!$template) return;` -> 2-op alt. render() is an
 * intra-object self-call (string token substitution) -> OMITTED (sequence shows
 * inter-object messages only; its output rides on the queue() label). The Mail
 * facade fluent chain to(toEmail)->queue(new TemplatedMail(...)) splits into 2
 * messages so the [template ditemukan] TOP operand is multi-msg (a 1-msg top
 * operand cramps its long label against the guard row). [template tidak
 * ditemukan] = silent return to caller, 1 msg to actor. EXACT structural twin
 * of UC-15 (2-msg-top / 1-msg-to-actor bottom): queue() is the top operand's
 * LAST msg, so the divider crowds it -> lower gapAbove (seesaw: top-last-crowds
 * -> toward 22). Pads = UC-15's confirmed recipe topPad 26 / gapAbove 22 /
 * botPad 34 (gapAbove 40 was wrong here -- that is the 1-msg-bottom-guard rule,
 * but it is the TOP msg that crowds). Caller lane = "Sistem"
 * (dispatch is invoked internally by other services on domain events).
 */
var UC28 = {
    ucId: "UC-28",
    title: "Kirim Notifikasi Email",
    lifelines: [
        "Sistem",
        "EmailNotificationService",
        "EmailTemplate",
        "Mail"
    ],
    messages: [
        { from: 0, to: 1, name: "dispatch(key, toEmail, payload)",            seq: 1 },
        { from: 1, to: 2, name: "where('key', key)->first()",                 seq: 2 },
        // alt [template ditemukan]
        { from: 1, to: 3, name: "to(toEmail)",                                seq: 3 },
        { from: 1, to: 3, name: "queue(new TemplatedMail(subject, body, key))", seq: 4 },
        // alt [template tidak ditemukan]
        { from: 1, to: 0, name: "return (tanpa kirim)",                       seq: 5 }
    ],
    alt: {
        firstSeq: 3, lastSeq: 5, leftIdx: 0, rightIdx: 3,
        // topPad 34: to(toEmail) was crowding the alt tab at 26 -> box top up.
        // gapAbove 32: at 22 the bottom-op return() had only 22-18=4px above it;
        // raising the divider gives return ~14px top. queue() (top-op last) had a
        // generous margin at 22, so it absorbs the 10px divider shift and still
        // clears. topPad/gapAbove are independent (divider = yOf(5)-gapAbove).
        topPad: 34, gapAbove: 32, botPad: 34,
        operands: [
            { guard: "template ditemukan",       firstSeq: 3, lastSeq: 4 },
            { guard: "template tidak ditemukan", firstSeq: 5, lastSeq: 5 }
        ]
    }
};

/* ============================== UC-04 data ============================== */
/*
 * Lihat Profil Sendiri (display, LINEAR -- NO alt). A karyawan hits
 * DashboardController.index; the non-HR branch is `return view('dashboard')`.
 * The own-profile data (unit, jabatan, info kepegawaian) is pulled in Blade via
 * the auth user->employee relation -- modelled here as a load on Employee so the
 * diagram conveys "melihat data dirinya" (the controller itself only checks the
 * role and renders; this one relation message is view-layer, included for
 * meaning). E1 "akun belum tertaut data karyawan" is a Blade @if empty-state,
 * NOT a controller branch -> no alt (two identical view() operands would be
 * fabricated), same call as UC-14/20. Linear, no combined fragment.
 */
var UC04 = {
    ucId: "UC-04",
    title: "Lihat Profil Sendiri",
    lifelines: [
        "Karyawan",
        "DashboardController",
        "User",
        "Employee"
    ],
    messages: [
        { from: 0, to: 1, name: "index(request)",                         seq: 1 },
        { from: 1, to: 2, name: "isHrAdmin()",                            seq: 2 },
        { from: 1, to: 3, name: "auth()->user()->employee (unit, jabatan)", seq: 3 },
        { from: 1, to: 0, name: "view('dashboard')",                      seq: 4 }
    ]
};

/* ================================ main ================================ */

function resolveTargetPackage(repo) {
    var sel = null;
    try { sel = repo.GetTreeSelectedObject(); } catch (e) { sel = null; }
    if (sel === null) { return null; }
    var ot = sel.ObjectType; // 5=package, 4=element, 8=diagram
    if (ot === 5) { return sel; }
    if (ot === 4 || ot === 8) {
        try { return repo.GetPackageByID(sel.PackageID); } catch (e2) { return null; }
    }
    return null;
}

function main() {
    var repo = Repository;
    var pkg = resolveTargetPackage(repo);
    if (pkg === null) {
        Session.Output("NO TARGET PACKAGE. Single-click a package in the Project");
        Session.Output("Browser (the one to receive the diagram), then re-run.");
        return;
    }
    Session.Output("Target package: " + pkg.Name);

    // UC16 is the calibrated exemplar (already committed). MODELS = batch to
    // render. Run on a FRESH empty package; each UC gets its own diagram.
    // Batch 6 (FINAL pipeline tail) = UC-01/02/12/13/18/27/29. UC-01 login
    // (Fortify, 2-op alt; the two ValidationException failures share one interaction
    // so they collapse to one gagal operand; success split into 2 msgs to avoid a
    // cramped 1-msg top operand -> multi-top/1-bottom, 34/34/34). UC-02 password
    // (CRUD-update twin 26/34/34).
    // UC-12 vacancy store (publish-guard 2-op, 2 pre-alt -> topPad 34). UC-13
    // snapshot copy (LINEAR, real createFromTemplate body). UC-18 test config
    // (valid/invalid 2-op, opaque snapshot factory, 26/34/34). UC-27 onboarding
    // (two-phase: sendInvitation linear + complete's isAdvanceable 2-op, 26/22/34).
    // UC-29 reserved («extend», UC-24 twin 26/22/34). UC-04 DEFERRED (no dedicated
    // "profil sendiri" controller -- DashboardController's isHrAdmin branch is
    // dashboard-metrics, not profil; flagged to user). Batches 1-5 committed.
    var MODELS = [UC28, UC04];
    var totalIssues = 0, i;
    for (i = 0; i < MODELS.length; i++) {
        totalIssues += renderUC(repo, pkg, MODELS[i]);
    }
    Session.Output("DONE. " + MODELS.length + " diagram(s), " + totalIssues +
        " sanity issue(s). Eyeball each alt box; if a box mis-encloses, report" +
        " px off and which UC.");
}

main();
