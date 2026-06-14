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

// PREDICTED message stacking (CALIBRATE on first UC-16 render):
var MSG_ORIGIN = 90;     // y of the FIRST message (seqNo 1)
var MSG_PITCH  = 45;     // vertical gap EA leaves between consecutive messages

// alt fragment box padding around the messages it encloses
var FRAG_PAD_X   = 24;   // box extends this far left/right beyond outermost lifeline
var FRAG_TOP_PAD = 24;   // room above first enclosed msg for the "alt" operator tab
var FRAG_BOT_PAD = 16;   // room below last enclosed msg
var OPERAND_GUARD_H = 4; // EA adds a little height per operand for its guard row

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
        var lx = LIFE_X0 + (a.leftIdx * LIFE_STEP) - FRAG_PAD_X;
        var rx = LIFE_X0 + (a.rightIdx * LIFE_STEP) + LIFE_W + FRAG_PAD_X;
        var topY = yOf(a.firstSeq) - FRAG_TOP_PAD;
        var botY = yOf(a.lastSeq) + FRAG_BOT_PAD;

        var fragEl = pkg.Elements.AddNew("", "InteractionFragment");
        fragEl.Update();
        place(dia, fragEl, lx, topY, rx - lx, botY - topY);

        // operand region heights from each operand's enclosed message span
        var operands = [];
        var k;
        for (k = 0; k < a.operands.length; k++) {
            var op = a.operands[k];
            var hgt = (yOf(op.lastSeq) - yOf(op.firstSeq)) + MSG_PITCH + OPERAND_GUARD_H;
            operands.push({ guard: op.guard, size: Math.round(hgt) });
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
    renderUC(repo, pkg, UC16);
    Session.Output("DONE. If the alt box does not enclose msgs 3-10, report how");
    Session.Output("many px off (top/bottom) and adjust MSG_ORIGIN / MSG_PITCH.");
}

main();
