/*
 * ea-seq-probe3.js  --  Sparx Enterprise Architect 15.2, JScript (WSH/ES3)
 *
 * THE STABILITY PROBE. ES3 ONLY: var, classic for loops, string concat.
 * No let/const/arrow/forEach/template-literals.
 *
 * WHY THIS EXISTS
 *   ea-sequence-build.js placed the alt box from a PREDICTED message Y
 *   (MSG_ORIGIN + MSG_PITCH * seq). Those constants were guessed; eyeball
 *   tweaking never converged; the box border collided with message lines.
 *   With activation bars mandatory ON, EA's vertical pitch is not something
 *   two constants can capture. The fix is two-pass: render messages, READ the
 *   actual rendered Y, then place the box from real coordinates.
 *
 *   But two-pass only works if THREE things hold. This probe tests all three
 *   in ONE run (EA round-trips with a human are the expensive resource):
 *
 *     Q1 READABLE  -- is a message's Y readable from the API after layout?
 *                     (We mirror the old reload sequence: ReloadDiagram THEN
 *                      OpenDiagram before every read. ReloadDiagram alone may
 *                      return nulls and give a false "not readable".)
 *     Q2 SHAPE     -- are the gaps uniform or non-uniform? (Reported, NOT
 *                     assumed. If uniform, the predecessor merely had the wrong
 *                     pitch; if non-uniform, only measurement can work. Either
 *                     way two-pass is correct -- this is for our information.)
 *     Q3 STABLE    -- does ADDING the alt box re-flow the messages? If the Y we
 *                     measured shifts the instant we insert the fragment, the
 *                     measurement is stale and the box collides anyway. This is
 *                     the load-bearing test and the likely cause of death.
 *
 *   It renders a throwaway diagram "PROBE3-seq", dumps Y, adds the box, dumps
 *   Y again, and diffs. Run it, paste ALL Scripting-tab output back.
 *
 * RUN
 *   1. Project Browser: single-click the PACKAGE to receive the throwaway
 *      diagram (any scratch package is fine -- this diagram is disposable).
 *   2. Specialize > Tools > Scripting > new JScript > paste this file > Run.
 *   3. Copy EVERYTHING from the Scripting output tab back.
 *
 * It WRITES a throwaway diagram (lifelines, messages, one alt box). Delete
 * "PROBE3-seq" afterwards. It touches nothing else.
 */

/* ============================== primitives ============================== */

function out(s) { Session.Output(s); }
function line() { out("------------------------------------------------------------"); }

function safe(fn) {
    try {
        var v = fn();
        if (v === null || typeof v === "undefined") { return "<null>"; }
        return "" + v;
    } catch (e) { return "<err:" + e.description + ">"; }
}

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

/* layout constants (only horizontal/extent ones matter here -- the WHOLE point
 * is that we do NOT predict message Y) */
var LIFE_X0   = 60;
var LIFE_STEP = 200;
var LIFE_W    = 110;
var LIFE_TOP  = 30;
var LIFE_FOOT = 600;   // make lifelines long; we don't know real msg span yet

function addLifeline(pkg, name) {
    var e = pkg.Elements.AddNew(name, "Sequence");
    e.Update();
    return e;
}

function place(dia, el, l, t, w, h) {
    var geo = "l=" + l + ";r=" + (l + w) + ";t=" + t + ";b=" + (t + h) + ";";
    var dobj = dia.DiagramObjects.AddNew(geo, "");
    dobj.ElementID = el.ElementID;
    dobj.Update();
    return dobj;
}

function addMessage(srcEl, tgtEl, name, seqNo) {
    var c = srcEl.Connectors.AddNew(name, "Sequence");
    c.SupplierID = tgtEl.ElementID;
    c.SequenceNo = seqNo;
    c.Update();
    srcEl.Connectors.Refresh();
    return c;
}

/* mirror the original build's persistence step: Reload THEN Open before reads */
function reloadAndOpen(repo, diaId) {
    try { repo.ReloadDiagram(diaId); } catch (e1) {}
    try { repo.OpenDiagram(diaId); } catch (e2) {}
}

/* ===================== message-Y reader (discovery) ====================== */
/*
 * We do not yet KNOW which field holds a sequence message's vertical position,
 * so this dumps every candidate: the DiagramLink Geometry/Path strings (EA
 * often stores "SX=..;SY=..;EX=..;EY=.." here) plus a raw t_diagramlinks query.
 * The numbers in the output tell us where Y actually lives.
 */
function readMessageYs(repo, dia) {
    var rows = [];
    var links = dia.DiagramLinks;
    var k;
    for (k = 0; k < links.Count; k++) {
        var dl = links.GetAt(k);
        var con = repo.GetConnectorByID(dl.ConnectorID);
        if (("" + con.Type) !== "Sequence") { continue; }
        rows.push({
            seq:   safe(function () { return con.SequenceNo; }),
            name:  safe(function () { return con.Name; }),
            geom:  safe(function () { return dl.Geometry; }),
            path:  safe(function () { return dl.Path; }),
            instId: dl.InstanceID
        });
    }
    return rows;
}

/* pull the first integer following key= (e.g. "SY=") out of a geometry blob */
function pickNum(blob, key) {
    if (!blob) { return null; }
    var idx = blob.indexOf(key);
    if (idx < 0) { return null; }
    var rest = blob.substring(idx + key.length);
    var m = rest.match(/-?\d+/);
    return m ? parseInt(m[0], 10) : null;
}

function dumpYs(repo, dia, label) {
    out("==== MESSAGE Y -- " + label + " ====");
    var rows = readMessageYs(repo, dia);
    if (rows.length === 0) {
        out("  <no Sequence-type DiagramLinks found>");
        return rows;
    }
    var i;
    for (i = 0; i < rows.length; i++) {
        var r = rows[i];
        out("  seq " + r.seq + "  '" + r.name + "'");
        out("     Geometry = " + r.geom);
        out("     Path     = " + r.path);
        // best-guess extraction so we can eyeball uniformity straight away
        var sy = pickNum(r.geom, "SY=");
        var ey = pickNum(r.geom, "EY=");
        out("     parsed SY=" + sy + "  EY=" + ey);
    }
    // also dump the raw table for the whole diagram, in case Y lives elsewhere
    out("  ---- raw t_diagramlinks (Geometry column) ----");
    out("  " + safe(function () {
        return repo.SQLQuery(
            "SELECT Instance_ID, ConnectorID, Geometry FROM t_diagramlinks " +
            "WHERE DiagramID=" + dia.DiagramID);
    }));
    line();
    return rows;
}

/* compare two Y dumps message-by-message: did the box insertion move anything? */
function diffYs(before, after) {
    out("==== STABILITY DIFF (after box vs before box) ====");
    if (before.length !== after.length) {
        out("  COUNT CHANGED before=" + before.length + " after=" + after.length);
    }
    var moved = 0;
    var n = Math.min(before.length, after.length);
    var i;
    for (i = 0; i < n; i++) {
        var b = pickNum(before[i].geom, "SY=");
        var a = pickNum(after[i].geom, "SY=");
        var tag = (b === a) ? "same" : ("MOVED " + b + " -> " + a);
        if (b !== a) { moved++; }
        out("  seq " + before[i].seq + "  SY: " + tag);
    }
    out("  ---- " + moved + " message(s) moved by box insertion ----");
    if (moved === 0) {
        out("  STABLE: two-pass is safe. Measure Y, then place box.");
    } else {
        out("  UNSTABLE: box insertion re-flows messages. Two-pass needs a");
        out("  fixpoint loop OR a non-fragment containment strategy.");
    }
    line();
}

/* ============================ alt-box writer ============================ */

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
    var guid = fragEl.ElementGUID;
    repo.Execute(
        "INSERT INTO t_xref (XrefID, Name, Type, Behavior, Client, Description) VALUES (" +
        "'" + xref + "', 'Partitions', 'element property', '', '" + guid + "', '" +
        escSql(desc) + "')");
}

/* ============================== UC-16 data ============================== */

var LIFELINES = [
    "HR Admin",
    "CvScreeningController",
    "ApplicationPipelineService",
    "ApplicationStage",
    "EmailNotificationService"
];
var MESSAGES = [
    { from: 0, to: 1, name: "decide(request, lowongan, application)", seq: 1 },
    { from: 1, to: 3, name: "update(catatan, reviewed_by)",          seq: 2 },
    { from: 1, to: 2, name: "advance(application)",                  seq: 3 },
    { from: 2, to: 3, name: "update(status: Selesai/Aktif)",         seq: 4 },
    { from: 2, to: 4, name: "dispatch('transisi_tahap', ...)",       seq: 5 },
    { from: 1, to: 2, name: "fail(application)",                     seq: 6 },
    { from: 2, to: 3, name: "update(status: Gagal)",                 seq: 7 },
    { from: 2, to: 4, name: "dispatch('kandidat_ditolak', ...)",     seq: 8 },
    { from: 1, to: 2, name: "reserve(application)",                  seq: 9 },
    { from: 2, to: 3, name: "update(status: Reserved)",              seq: 10 }
];

/* ============================ target package ============================ */

function resolveTargetPackage(repo) {
    var sel = null;
    try { sel = repo.GetTreeSelectedObject(); } catch (e) { return null; }
    if (sel === null) { return null; }
    var ot = sel.ObjectType;
    if (ot === 5) { return sel; }
    if (ot === 4 || ot === 8) {
        try { return repo.GetPackageByID(sel.PackageID); } catch (e2) { return null; }
    }
    return null;
}

/* ================================ main ================================ */

function main() {
    var repo = Repository;
    line();
    out("EA SEQUENCE STABILITY PROBE (probe3)");
    out("EA library version : " + safe(function () { return repo.LibraryVersion; }));
    line();

    var pkg = resolveTargetPackage(repo);
    if (pkg === null) {
        out("NO TARGET PACKAGE. Single-click a scratch package in the Project");
        out("Browser, then re-run.");
        return;
    }
    out("Target package: " + pkg.Name);

    // fresh throwaway diagram
    var dia = pkg.Diagrams.AddNew("PROBE3-seq", "Sequence");
    dia.Update();
    pkg.Diagrams.Refresh();

    // PASS 1: lifelines + messages, NO box
    var lifeEls = [];
    var i;
    for (i = 0; i < LIFELINES.length; i++) {
        var le = addLifeline(pkg, LIFELINES[i]);
        place(dia, le, LIFE_X0 + (i * LIFE_STEP), LIFE_TOP, LIFE_W, LIFE_FOOT);
        lifeEls.push(le);
    }
    for (i = 0; i < MESSAGES.length; i++) {
        var m = MESSAGES[i];
        addMessage(lifeEls[m.from], lifeEls[m.to], m.name, m.seq);
    }
    reloadAndOpen(repo, dia.DiagramID);

    // Q1 READABLE + Q2 SHAPE: dump Y before any box
    var before = dumpYs(repo, dia, "BEFORE BOX");

    // PASS 2: insert the alt box (spanning lifelines 1..4, generous height)
    var lx = LIFE_X0 + (1 * LIFE_STEP) - 24;
    var rx = LIFE_X0 + (4 * LIFE_STEP) + LIFE_W + 24;
    var fragEl = pkg.Elements.AddNew("", "InteractionFragment");
    fragEl.Update();
    place(dia, fragEl, lx, 70, rx - lx, 400);
    configureAlt(repo, fragEl, [
        { guard: "lulus",        size: 130 },
        { guard: "gagal",        size: 130 },
        { guard: "ditangguhkan", size: 90 }
    ]);
    reloadAndOpen(repo, dia.DiagramID);

    // Q3 STABLE: dump Y again, diff
    var after = dumpYs(repo, dia, "AFTER BOX");
    diffYs(before, after);

    // also show the box's stored geometry so we can confirm we can READ BACK
    // both the outer box and (for operand dividers) the Partitions blob.
    out("==== ALT BOX read-back ====");
    out("  t_object PDATA1 (operator) + geometry:");
    out("  " + safe(function () {
        return repo.SQLQuery(
            "SELECT Object_ID, PDATA1 FROM t_object WHERE Object_ID=" + fragEl.ElementID);
    }));
    out("  t_xref Partitions blob (operand divider sizes):");
    out("  " + safe(function () {
        return repo.SQLQuery(
            "SELECT Name, Description FROM t_xref WHERE Client='" + fragEl.ElementGUID + "'");
    }));
    line();

    out("PROBE3 DONE. Paste ALL output back. Then delete the PROBE3-seq diagram.");
    out("Decision: READABLE? + gaps uniform/non-uniform? + STABLE under box?");
}

main();
