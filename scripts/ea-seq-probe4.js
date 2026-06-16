/*
 * ea-seq-probe4.js  --  Sparx Enterprise Architect 15.2, JScript (WSH/ES3)
 *
 * THE PITCH-CONTROL PROBE. ES3 ONLY: var, classic for loops, string concat.
 *
 * WHY
 *   probe3 proved EA stores NO Y for an AUTO-laid message (Geometry/Path empty,
 *   t_diagramlinks zero rows) -- EA stacks messages at a FIXED ~41px pitch. That
 *   pitch is the ceiling on how much clearance an alt operand can give its first
 *   message: in a 41px gap, a divider that clears the lower message crowds the
 *   upper one. The only way to get real breathing room is a WIDER pitch, i.e.
 *   we set each message's Y ourselves. That only works if EA (a) PERSISTS a
 *   manually-placed message Y and (b) exposes it in a field we can WRITE back.
 *
 *   This probe answers both by reading what EA stored AFTER YOU DRAG messages.
 *
 * DO THIS FIRST (manual)
 *   1. Open the generated "UC-16 Skrining CV HR" sequence diagram.
 *   2. Grab 2-3 messages and DRAG them to clearly NEW vertical positions
 *      (e.g. pull 'advance' and 'fail' much further down than EA placed them).
 *      Spread them unevenly so the stored numbers are easy to tell apart.
 *   3. Save the diagram (Ctrl+S).
 *
 * THEN
 *   - Single-click that diagram (or its package) in the Project Browser.
 *   - Specialize > Tools > Scripting > new JScript > paste this > Run.
 *   - Copy ALL Scripting-tab output back.
 *
 * Read-only: SELECT + property reads only. Changes nothing.
 */

function out(s) { Session.Output(s); }
function line() { out("------------------------------------------------------------"); }

function safe(fn) {
    try {
        var v = fn();
        if (v === null || typeof v === "undefined") { return "<null>"; }
        return "" + v;
    } catch (e) { return "<err:" + e.description + ">"; }
}

function resolveDiagram(repo) {
    var dia = null;
    try { dia = repo.GetCurrentDiagram(); } catch (e) { dia = null; }
    if (dia !== null) { return dia; }
    try {
        var sel = repo.GetTreeSelectedObject();
        if (sel !== null && sel.ObjectType === 8) { return sel; }
        if (sel !== null && sel.ObjectType === 5 && sel.Diagrams.Count > 0) {
            return sel.Diagrams.GetAt(0);
        }
    } catch (e2) {}
    return null;
}

function main() {
    var repo = Repository;
    line();
    out("EA SEQUENCE PITCH-CONTROL PROBE (probe4)");
    line();

    var dia = resolveDiagram(repo);
    if (dia === null) {
        out("NO DIAGRAM. Open the UC-16 sequence diagram (or single-click its");
        out("package), then re-run.");
        return;
    }
    out("Diagram: " + safe(function () { return dia.Name; }) +
        "  DiagramID=" + safe(function () { return dia.DiagramID; }));
    line();

    // 1. per-message DiagramLink geometry, post-drag. If these are now NON-empty
    //    we have both the storage location AND the exact format to write.
    out("MESSAGE DIAGRAMLINKS (after manual drag)");
    var links = dia.DiagramLinks;
    out("  DiagramLinks.Count = " + links.Count);
    var k;
    for (k = 0; k < links.Count; k++) {
        var dl = links.GetAt(k);
        var con = repo.GetConnectorByID(dl.ConnectorID);
        if (("" + con.Type) !== "Sequence") { continue; }
        out("  ----");
        out("  seq " + safe(function () { return con.SequenceNo; }) +
            "  '" + safe(function () { return con.Name; }) + "'");
        out("     Instance_ID = " + safe(function () { return dl.InstanceID; }));
        out("     Geometry    = " + safe(function () { return dl.Geometry; }));
        out("     Path        = " + safe(function () { return dl.Path; }));
    }
    line();

    // 2. raw t_diagramlinks for this diagram. Only the columns probe3 proved
    //    exist (Instance_ID, ConnectorID, Geometry) -- adding Path/DiagramID to
    //    the SELECT triggers DAO "Too few parameters. Expected 1." (unknown
    //    column read as a query parameter).
    out("RAW t_diagramlinks (Geometry column)");
    out(safe(function () {
        return repo.SQLQuery(
            "SELECT Instance_ID, ConnectorID, Geometry " +
            "FROM t_diagramlinks WHERE DiagramID=" + dia.DiagramID);
    }));
    line();

    out("PROBE4 DONE. Paste ALL output back. KEY QUESTION: did any Geometry/Path");
    out("become NON-empty after dragging? If yes -> we can write that string to");
    out("set a wider pitch (e.g. 65px) and every message line gets real margin.");
}

main();
