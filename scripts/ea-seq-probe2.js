/*
 * ea-seq-probe2.js  --  Sparx EA 15.2, JScript (WSH/ES3)  --  FRAGMENT PROBE
 *
 * Probe 1 found everything EXCEPT how a Combined Fragment stores its operator
 * (alt/opt) and its operand GUARDS ([lulus]/[gagal]). The Automation object
 * model didn't expose them (no child Elements, no TaggedValues). This probe
 * reads the raw EA tables via Repository.SQLQuery to find where they live.
 *
 * BEFORE RUNNING -- make the reference fragment fully configured:
 *   1. Open PROBE-seq. Double-click the Combined Fragment.
 *   2. Confirm Operator = alt.
 *   3. On the Operands/Regions tab: ensure there are TWO operands, and type a
 *      guard on each:  operand 1 guard = lulus ,  operand 2 guard = gagal .
 *   4. OK / Save. (This is the step that was likely incomplete before.)
 *   5. ALSO: right-click the m3 ("ok") message > set it to a RETURN / reply
 *      message (look for "Is Return Message" or lifecycle/return option) so we
 *      capture how a return differs from a call. Save the diagram.
 *
 * THEN: single-click PROBE-seq in the Project Browser, run this in
 * Specialize > Tools > Scripting (new JScript), copy ALL output back.
 *
 * Read-only (SELECT queries only). Changes nothing.
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

// run a SELECT and dump the raw XML EA returns (we read the column tags by eye)
function dumpSQL(repo, label, sql) {
    out("==== " + label + " ====");
    out("SQL: " + sql);
    var res = "";
    try {
        res = repo.SQLQuery(sql);
    } catch (e) {
        out("  <SQLQuery err: " + e.description + ">");
        return;
    }
    if (res === null || res === "") {
        out("  <empty result>");
        return;
    }
    out(res);
    line();
}

function main() {
    var repo = Repository;
    line();
    out("EA SEQUENCE FRAGMENT PROBE (raw tables)");
    line();

    // resolve the diagram (same fallback as probe 1) just to find the fragment ID
    var dia = null;
    try { dia = repo.GetCurrentDiagram(); } catch (e) { dia = null; }
    if (dia === null) {
        try {
            var sel = repo.GetTreeSelectedObject();
            if (sel !== null && sel.ObjectType === 8) { dia = sel; }
            else if (sel !== null && sel.ObjectType === 5 && sel.Diagrams.Count > 0) { dia = sel.Diagrams.GetAt(0); }
        } catch (e2) {}
    }
    if (dia === null) {
        out("NO DIAGRAM. Double-click PROBE-seq to open it, then re-run.");
        return;
    }
    out("Diagram: " + safe(function () { return dia.Name; }) +
        "  DiagramID=" + safe(function () { return dia.DiagramID; }));

    // find the InteractionFragment element id on this diagram
    var fragId = -1;
    var objs = dia.DiagramObjects;
    var i;
    for (i = 0; i < objs.Count; i++) {
        var el = repo.GetElementByID(objs.GetAt(i).ElementID);
        if (("" + el.Type) === "InteractionFragment") {
            fragId = el.ElementID;
            out("Found InteractionFragment ElementID = " + fragId);
        }
    }
    if (fragId < 0) {
        out("No InteractionFragment on this diagram. Did you draw the alt box?");
        return;
    }
    line();

    // 1. the fragment's own t_object row -- operator often in PDATA1..5 or Note
    dumpSQL(repo, "t_object (the fragment)",
        "SELECT Object_ID, Object_Type, Name, Stereotype, PDATA1, PDATA2, PDATA3, PDATA4, PDATA5, Note, ParentID FROM t_object WHERE Object_ID=" + fragId);

    // 2. any child objects parented to the fragment (operands live here in some EA versions)
    dumpSQL(repo, "t_object children (ParentID = fragment)",
        "SELECT Object_ID, Object_Type, Name, Stereotype, PDATA1, PDATA2, Note FROM t_object WHERE ParentID=" + fragId);

    // 3. tagged-value table rows for the fragment
    dumpSQL(repo, "t_objectproperties (tagged values)",
        "SELECT Property, Value, Notes FROM t_objectproperties WHERE Object_ID=" + fragId);

    // 4. operands sometimes stored as t_operation rows on the fragment
    dumpSQL(repo, "t_operation (operands as operations?)",
        "SELECT OperationID, Name, Notes, Behaviour, Code FROM t_operation WHERE Object_ID=" + fragId);

    // 5. attributes (operands sometimes stored as attributes with guards in Notes/Default)
    dumpSQL(repo, "t_attribute (operands as attributes?)",
        "SELECT Name, Notes, [Default], Stereotype FROM t_attribute WHERE Object_ID=" + fragId);

    // 6. xref rows (EA stows structured behaviour in t_xref.Description as a blob).
    // ElementGUID already includes the surrounding braces -- do not add more.
    var fragGuid = safe(function () { return repo.GetElementByID(fragId).ElementGUID; });
    dumpSQL(repo, "t_xref (behaviour blobs for the fragment)",
        "SELECT XrefID, Name, Type, Behavior, Description FROM t_xref WHERE Client='" + fragGuid + "'");

    out("FRAGMENT PROBE DONE. Copy everything above back -- esp. any row whose");
    out("text contains 'alt', 'lulus', or 'gagal'.");
}

main();
