/*
 * ea-activity-build.js  --  Sparx Enterprise Architect 15.2, JScript (WSH/ES3)
 *
 * Builds UML Activity diagrams from a data model. ES3 ONLY: var, classic for
 * loops, string concat. No let/const/arrow/forEach/template-literals.
 *
 * Constants below were read off a hand-drawn EA 15.2 diagram (see
 * ea-activity-probe.js output), not guessed:
 *   Initial      = Type "StateNode", Subtype 100
 *   ActivityFinal= Type "StateNode", Subtype 101
 *   FlowFinal    = Type "StateNode", Subtype 102
 *   Action       = Type "Action"
 *   Decision     = Type "Decision"
 *   Swimlane     = Type "ActivityPartition" (membership by geometry/X position)
 *   Connector    = Type "ControlFlow" (branch label via TransitionGuard)
 *   Geometry     = X positive; in AddNew pass POSITIVE t/b with t<b, EA negates.
 *
 * RUN
 *   1. Project Browser: single-click the PACKAGE that should receive the
 *      diagram (e.g. a package named "Diagram Aktivitas").
 *   2. Specialize > Tools > Scripting > new JScript > paste this file > Run.
 *   3. EA opens the generated diagram. Eyeball connector routing.
 *
 * This is the go/no-go exemplar: ONE branch-heavy UC (UC-16) at FULL fidelity
 * (main flow + A1/A2 + E1/E2). If its connectors route cleanly, the same
 * engine scales to all 37 UCs.
 */

/* ======================= grid / layout constants ======================= */

var LANE_A_L = 40;      // Aktor partition left edge (fixed)
var SUB_STEP = 155;     // horizontal step between sub-columns inside a lane
var SUB_PAD  = 30;      // left pad inside a lane before col 0
var SLOT_W   = 130;     // nominal slot width a node centers within
var ROW_TOP  = 80;      // y of row 0 (leaves room for partition header)
var ROW_STEP = 80;      // vertical step per row

// Lane geometry is DATA-DRIVEN: widths/Sistem-left are computed per diagram
// from the max column used in each lane (see computeLanes). These globals are
// (re)assigned by renderUC before any node is placed.
var LANE_A_W = 470;
var LANE_S_L = LANE_A_L + LANE_A_W;
var LANE_S_W = 470;

// width a lane needs to hold columns 0..maxCol
function laneWidth(maxCol) {
    return (2 * SUB_PAD) + (maxCol * SUB_STEP) + SLOT_W;
}

// scan nodes, set the lane globals so both partitions fit their columns
function computeLanes(nodes) {
    var maxA = 0, maxS = 0, i;
    for (i = 0; i < nodes.length; i++) {
        var n = nodes[i];
        if (n.lane === "S") { if (n.col > maxS) { maxS = n.col; } }
        else { if (n.col > maxA) { maxA = n.col; } }
    }
    LANE_A_W = laneWidth(maxA);
    LANE_S_L = LANE_A_L + LANE_A_W;
    LANE_S_W = laneWidth(maxS);
}

/* ============================= primitives ============================= */

function laneLeft(lane) {
    return (lane === "S") ? LANE_S_L : LANE_A_L;
}

// left x of the SLOT_W slot for a given lane+col
function slotX(lane, col) {
    return laneLeft(lane) + SUB_PAD + (col * SUB_STEP);
}

function rowY(row) {
    return ROW_TOP + (row * ROW_STEP);
}

// node pixel size by kind
function nodeW(kind) {
    if (kind === "action") { return SLOT_W; }
    if (kind === "decision") { return 50; }
    return 30; // initial / final / flowfinal
}
function nodeH(kind) {
    if (kind === "action") { return 44; }
    if (kind === "decision") { return 40; }
    return 30;
}

function eaType(kind) {
    if (kind === "action") { return "Action"; }
    if (kind === "decision") { return "Decision"; }
    return "StateNode"; // initial / final / flowfinal
}
function eaSubtype(kind) {
    if (kind === "initial") { return 100; }
    if (kind === "final") { return 101; }
    if (kind === "flowfinal") { return 102; }
    return 0;
}

function addElement(pkg, name, kind) {
    var e = pkg.Elements.AddNew(name, eaType(kind));
    e.Update();
    var st = eaSubtype(kind);
    if (st !== 0) {
        e.Subtype = st;
        e.Update();
    }
    return e;
}

// place an element on the diagram; l/t positive, EA stores negated internally
function place(dia, el, l, t, w, h) {
    var r = l + w;
    var b = t + h;
    var geo = "l=" + l + ";r=" + r + ";t=" + t + ";b=" + b + ";";
    var dobj = dia.DiagramObjects.AddNew(geo, "");
    dobj.ElementID = el.ElementID;
    dobj.Update();
    return dobj;
}

function connect(src, tgt, guard) {
    var c = src.Connectors.AddNew("", "ControlFlow");
    c.SupplierID = tgt.ElementID;
    if (guard && guard.length > 0) {
        c.TransitionGuard = guard;
    }
    c.Update();
    src.Connectors.Refresh();
    return c;
}

// set/replace a "Key=value;" token inside a DiagramLink.Style string
function setStyleToken(style, key, val) {
    if (!style) { style = ""; }
    var re = new RegExp(key + "=[^;]*;");
    if (re.test(style)) {
        return style.replace(re, key + "=" + val + ";");
    }
    return style + key + "=" + val + ";";
}
function removeStyleToken(style, key) {
    if (!style) { return ""; }
    return style.replace(new RegExp(key + "=[^;]*;"), "");
}

// Baseline: every connector custom-mode orthogonal-square. The waypoint pass
// then overrides crossing edges with explicit channel routes.
function routeOrthogonal(dia) {
    dia.DiagramLinks.Refresh();
    var i;
    for (i = 0; i < dia.DiagramLinks.Count; i++) {
        var dl = dia.DiagramLinks.GetAt(i);
        var s = dl.Style;
        s = setStyleToken(s, "Mode", "3");
        s = setStyleToken(s, "TREE", "OS");
        dl.Style = s;
        dl.Update();
    }
}

/* ============================== the router ============================== */
/*
 * EA stores manual waypoints on DiagramLink.Path as "x:y;x:y;" in ABSOLUTE
 * diagram coords: x positive, y NEGATIVE (= -pixelY). We keep the spine
 * straight (no waypoints) and give every CROSSING edge its own horizontal
 * channel-Y so parallel runs never overlap -- the hand-tuned "Image 2" look.
 */
var CH_BASE = 12;   // first channel offset below the source
var CH_STEP = 11;   // vertical gap between sibling channels

function pt(px, py) {           // pixel (down-positive) -> EA Path token
    return px + ":" + (-py) + ";";
}
function cx(g) { return g.l + (g.w / 2); }   // center x
function findLink(dia, conId) {
    dia.DiagramLinks.Refresh();
    var i;
    for (i = 0; i < dia.DiagramLinks.Count; i++) {
        var dl = dia.DiagramLinks.GetAt(i);
        if (dl.ConnectorID === conId) { return dl; }
    }
    return null;
}

// Compute the deterministic polyline for every edge. Straight edges get a
// 2-point line (EA draws it); crossing edges get a 4-point Z through their own
// channel-Y plus the EA Path string. Returns an array of route objects.
var STUB_SPREAD = 9;  // horizontal fan of sibling stubs leaving one source

// classify an edge's routing geometry (shared by compute + analysis)
function edgeKind(s, t) {
    var sxc = cx(s), txc = cx(t);
    var sameCol = Math.abs(sxc - txc) < 6;
    var sameRow = Math.abs(s.t - t.t) < 6;
    return { sxc: sxc, txc: txc, sameCol: sameCol, sameRow: sameRow,
             goingDown: (t.t >= s.t), crossing: (!sameCol && !sameRow) };
}

function computeRoutes(geom, routed) {
    // pass 1: count crossing siblings per corridor key (to center the fan)
    var keyTotal = {}, r, info = [];
    for (r = 0; r < routed.length; r++) {
        var s0 = geom[routed[r].from], t0 = geom[routed[r].to];
        if (!s0 || !t0) { info.push(null); continue; }
        var ek = edgeKind(s0, t0);
        info.push(ek);
        if (ek.crossing) {
            var key0 = "" + Math.round(s0.t) + "_" + (ek.goingDown ? "d" : "u");
            keyTotal[key0] = (keyTotal[key0] ? keyTotal[key0] : 0) + 1;
        }
    }

    // pass 2: build polylines
    var channel = {}, routes = [];
    for (r = 0; r < routed.length; r++) {
        var edge = routed[r];
        var s = geom[edge.from], t = geom[edge.to];
        if (!s || !t) { continue; }
        var ek2 = info[r];
        var points, pathStr = "";
        if (ek2.sameCol) {
            var sy = ek2.goingDown ? (s.t + s.h) : s.t;
            var ty = ek2.goingDown ? t.t : (t.t + t.h);
            points = [{ x: ek2.sxc, y: sy }, { x: ek2.txc, y: ty }];
        } else if (ek2.sameRow) {
            var my = s.t + (s.h / 2);
            var sx = (ek2.txc > ek2.sxc) ? (s.l + s.w) : s.l;
            var ex = (ek2.txc > ek2.sxc) ? t.l : (t.l + t.w);
            points = [{ x: sx, y: my }, { x: ex, y: my }];
        } else {
            var key = "" + Math.round(s.t) + "_" + (ek2.goingDown ? "d" : "u");
            var k = channel[key] ? channel[key] : 0;
            channel[key] = k + 1;
            var tot = keyTotal[key];
            var stubX = ek2.sxc + ((k - ((tot - 1) / 2)) * STUB_SPREAD);
            var chY = ek2.goingDown
                ? ((s.t + s.h) + CH_BASE + (k * CH_STEP))
                : (s.t - CH_BASE - (k * CH_STEP));
            var sY2 = ek2.goingDown ? (s.t + s.h) : s.t;
            var eY2 = ek2.goingDown ? t.t : (t.t + t.h);
            points = [{ x: stubX, y: sY2 }, { x: stubX, y: chY },
                      { x: ek2.txc, y: chY }, { x: ek2.txc, y: eY2 }];
            pathStr = pt(stubX, chY) + pt(ek2.txc, chY);
        }
        routes.push({ from: edge.from, to: edge.to, conId: edge.conId,
                      points: points, pathStr: pathStr });
    }
    return routes;
}

// Write the Path waypoints for crossing edges onto their DiagramLinks.
function applyRoutes(dia, routes) {
    var r;
    for (r = 0; r < routes.length; r++) {
        var rt = routes[r];
        if (rt.pathStr === "") { continue; }
        var dl = findLink(dia, rt.conId);
        if (dl === null) { continue; }
        dl.Path = rt.pathStr;
        dl.Style = removeStyleToken(setStyleToken(dl.Style, "Mode", "3"), "TREE");
        dl.Update();
    }
}

/* ========================= collision analyzer ========================= */
/*
 * Predicts overlaps WITHOUT a screenshot: reconstructs each connector's
 * axis-aligned segments and reports (1) segments that pass through a node
 * rectangle they don't belong to, and (2) collinear connector segments that
 * overlap. Pure perpendicular crossings are normal and NOT flagged.
 */
var NODE_MARGIN = 3;  // shrink rects so a line touching an edge isn't a hit

function between(v, a, b) {
    var lo = (a < b) ? a : b;
    var hi = (a < b) ? b : a;
    return (v > lo) && (v < hi);
}
function overlapLen(a1, a2, b1, b2) {
    var aLo = (a1 < a2) ? a1 : a2, aHi = (a1 < a2) ? a2 : a1;
    var bLo = (b1 < b2) ? b1 : b2, bHi = (b1 < b2) ? b2 : b1;
    var lo = (aLo > bLo) ? aLo : bLo;
    var hi = (aHi < bHi) ? aHi : bHi;
    return hi - lo;
}
function rangesOverlap(a1, a2, b1, b2) {
    return overlapLen(a1, a2, b1, b2) > 4; // real overlap, not a shared corner
}

// does an axis-aligned segment pass through the interior of rect g?
function segHitsRect(p1, p2, g) {
    var L = g.l + NODE_MARGIN, R = g.l + g.w - NODE_MARGIN;
    var T = g.t + NODE_MARGIN, B = g.t + g.h - NODE_MARGIN;
    if (Math.abs(p1.y - p2.y) < 2) {            // horizontal seg
        var y = p1.y;
        if (!between(y, T - 2 * NODE_MARGIN, B + 2 * NODE_MARGIN)) { return false; }
        if (y <= T || y >= B) { return false; }
        return rangesOverlap(p1.x, p2.x, L, R);
    } else {                                     // vertical seg
        var x = p1.x;
        if (x <= L || x >= R) { return false; }
        return rangesOverlap(p1.y, p2.y, T, B);
    }
}

function segsOf(route) {
    var segs = [];
    var i;
    for (i = 0; i < route.points.length - 1; i++) {
        segs.push({ a: route.points[i], b: route.points[i + 1] });
    }
    return segs;
}

// returns the collinear overlap length between two segments (0 if not collinear)
function collinearOverlap(sA, sB) {
    var aH = Math.abs(sA.a.y - sA.b.y) < 2;
    var bH = Math.abs(sB.a.y - sB.b.y) < 2;
    if (aH && bH && Math.abs(sA.a.y - sB.a.y) < 3) {
        var lh = overlapLen(sA.a.x, sA.b.x, sB.a.x, sB.b.x);
        return (lh > 4) ? lh : 0;
    }
    var aV = Math.abs(sA.a.x - sA.b.x) < 2;
    var bV = Math.abs(sB.a.x - sB.b.x) < 2;
    if (aV && bV && Math.abs(sA.a.x - sB.a.x) < 3) {
        var lv = overlapLen(sA.a.y, sA.b.y, sB.a.y, sB.b.y);
        return (lv > 4) ? lv : 0;
    }
    return 0;
}

function analyzeRoutes(routes, nodes, geom) {
    Session.Output("---- COLLISION ANALYSIS ----");
    var hits = 0;
    var r, n, sg;
    // 1. connector through a foreign node
    for (r = 0; r < routes.length; r++) {
        var rt = routes[r];
        var segs = segsOf(rt);
        for (n = 0; n < nodes.length; n++) {
            var nd = nodes[n];
            if (nd.id === rt.from || nd.id === rt.to) { continue; }
            if (nd.kind === "decision" || nd.kind === "action") {
                var g = geom[nd.id];
                for (sg = 0; sg < segs.length; sg++) {
                    if (segHitsRect(segs[sg].a, segs[sg].b, g)) {
                        Session.Output("  THRU-NODE: edge " + rt.from + "->" + rt.to +
                            " crosses node [" + nd.id + "] " + (nd.text ? nd.text : nd.kind));
                        hits++;
                        break;
                    }
                }
            }
        }
    }
    // 2. collinear connector overlap (long = real issue, short = exit-bus stub)
    var OVERLAP_MIN = 30;  // below this, treat as the acceptable decision-exit bus
    var minor = 0;
    var a, b;
    for (a = 0; a < routes.length; a++) {
        var sa = segsOf(routes[a]);
        for (b = a + 1; b < routes.length; b++) {
            var sb = segsOf(routes[b]);
            var i2, j2, best = 0;
            for (i2 = 0; i2 < sa.length; i2++) {
                for (j2 = 0; j2 < sb.length; j2++) {
                    var ol = collinearOverlap(sa[i2], sb[j2]);
                    if (ol > best) { best = ol; }
                }
            }
            if (best > 0) {
                var tag = (best >= OVERLAP_MIN) ? "OVERLAP" : "minor-stub";
                Session.Output("  " + tag + " (" + Math.round(best) + "px): edge " +
                    routes[a].from + "->" + routes[a].to + "  ||  " +
                    routes[b].from + "->" + routes[b].to);
                if (best >= OVERLAP_MIN) { hits++; } else { minor++; }
            }
        }
    }
    if (hits === 0) { Session.Output("  CLEAN: no THRU-NODE or long overlaps (" + minor + " minor stub(s))."); }
    Session.Output("---- " + hits + " real issue(s), " + minor + " minor ----");
    return hits;
}

/* ============================ render engine ============================ */

// model = { ucId, title, actorLane, maxRow, nodes:[{id,kind,lane,col,row,text}],
//           edges:[{from,to,guard}] }
function renderUC(repo, pkg, model) {
    // 1. diagram
    var dia = pkg.Diagrams.AddNew(model.ucId + " " + model.title, "Activity");
    dia.Update();
    pkg.Diagrams.Refresh();

    // 2. partitions first (z-order: behind the nodes). Widths are data-driven.
    computeLanes(model.nodes);
    var laneBottom = rowY(model.maxRow) + 60;
    var pA = pkg.Elements.AddNew(model.actorLane, "ActivityPartition");
    pA.Update();
    place(dia, pA, LANE_A_L, 20, LANE_A_W, laneBottom);
    var pS = pkg.Elements.AddNew("Sistem", "ActivityPartition");
    pS.Update();
    place(dia, pS, LANE_S_L, 20, LANE_S_W, laneBottom);

    // 3. nodes  (also remember pixel geometry for the router)
    var byId = {};
    var geom = {};
    var i;
    for (i = 0; i < model.nodes.length; i++) {
        var n = model.nodes[i];
        var nm = n.text ? n.text : "";
        var el = addElement(pkg, nm, n.kind);
        var w = nodeW(n.kind);
        var h = nodeH(n.kind);
        var l = slotX(n.lane, n.col) + ((SLOT_W - w) / 2); // center small nodes in slot
        var t = rowY(n.row);
        place(dia, el, l, t, w, h);
        byId[n.id] = el;
        geom[n.id] = { l: l, t: t, w: w, h: h };
    }

    // 4. edges  (capture each connector's ID so the router can find its link)
    var routed = [];
    var j;
    for (j = 0; j < model.edges.length; j++) {
        var e = model.edges[j];
        var s = byId[e.from];
        var d = byId[e.to];
        if (s && d) {
            var c = connect(s, d, e.guard ? e.guard : "");
            routed.push({ from: e.from, to: e.to, conId: c.ConnectorID });
        } else {
            Session.Output("WARN edge refers to missing node: " + e.from + " -> " + e.to);
        }
    }

    // 5. baseline orthogonal style, compute routes, apply waypoints, analyze
    routeOrthogonal(dia);
    var routes = computeRoutes(geom, routed);
    applyRoutes(dia, routes);
    Session.Output("== " + model.ucId + " " + model.title + " ==");
    var real = analyzeRoutes(routes, model.nodes, geom);

    repo.ReloadDiagram(dia.DiagramID);
    repo.OpenDiagram(dia.DiagramID);
    Session.Output("Built " + model.ucId + " (" + model.nodes.length +
        " nodes, " + model.edges.length + " edges).");
    return real;
}

/* ============================== UC-16 data ============================= */
/*
 * Skrining CV HR. Actor lane = "HR Admin", system lane = "Sistem".
 * Full fidelity: main flow + A1 (Gagal) + A2 (Ditangguhkan) +
 *                E1 (tahap sudah diputus) + E2 (tes belum dikonfigurasi).
 *
 * Layout principles (so the analyzer reports clean):
 *   - Each branch owns its OWN Sistem column, with a LOCAL ActivityFinal, so
 *     no long edge converges back across the spine.
 *   - No node is stacked in a column that another edge must travel through.
 *   Lane A cols 0..2 (E1 fans right); Lane S col0=lulus spine, col1=E2 sub,
 *   col2=gagal, col3=ditangguhkan.
 */
var UC16 = {
    ucId: "UC-16",
    title: "Skrining CV HR",
    actorLane: "HR Admin",
    maxRow: 9,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_review", kind: "action", lane: "A", col: 0, row: 1, text: "Tinjau CV (UC-15) & isi catatan" },
        { id: "d_active", kind: "decision", lane: "A", col: 0, row: 2, text: "Tahap Aktif / Ditangguhkan?" },
        { id: "a_e1msg", kind: "action", lane: "A", col: 1, row: 2, text: "Pesan: keputusan tidak dapat diberikan" },
        { id: "ff_e1", kind: "flowfinal", lane: "A", col: 2, row: 2 },
        { id: "a_decide", kind: "action", lane: "A", col: 0, row: 3, text: "Pilih keputusan & simpan" },
        { id: "d_kep", kind: "decision", lane: "A", col: 0, row: 4, text: "Keputusan skrining?" },

        // lulus spine (Sistem col 0)
        { id: "a_pass", kind: "action", lane: "S", col: 0, row: 5, text: "Simpan catatan & peninjau, tandai Selesai" },
        { id: "d_tes", kind: "decision", lane: "S", col: 0, row: 6, text: "Tahap berikut = Tes Kompetensi?" },
        { id: "a_activate", kind: "action", lane: "S", col: 0, row: 7, text: "Aktifkan tahap berikutnya" },
        { id: "a_mailpass", kind: "action", lane: "S", col: 0, row: 8, text: "Kirim email lolos skrining (UC-28)" },
        { id: "final_lulus", kind: "final", lane: "S", col: 0, row: 9 },

        // E2 sub-branch off d_tes (Sistem col 1)
        { id: "d_tescfg", kind: "decision", lane: "S", col: 1, row: 6, text: "Tes terkonfigurasi?" },
        { id: "a_e2msg", kind: "action", lane: "S", col: 1, row: 7, text: "Batalkan transaksi, pesan konfigurasi tes" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 1, row: 8 },

        // gagal branch (Sistem col 2) with local final
        { id: "a_fail", kind: "action", lane: "S", col: 2, row: 5, text: "Tandai Gagal, hentikan pipeline" },
        { id: "a_mailfail", kind: "action", lane: "S", col: 2, row: 6, text: "Kirim email penolakan (UC-28)" },
        { id: "final_gagal", kind: "final", lane: "S", col: 2, row: 7 },

        // ditangguhkan branch (Sistem col 3) with local final
        { id: "a_reserve", kind: "action", lane: "S", col: 3, row: 5, text: "Tandai Ditangguhkan (UC-29)" },
        { id: "final_tangguh", kind: "final", lane: "S", col: 3, row: 6 }
    ],
    edges: [
        { from: "init", to: "a_review" },
        { from: "a_review", to: "d_active" },
        { from: "d_active", to: "a_e1msg", guard: "sudah diputus" },
        { from: "a_e1msg", to: "ff_e1" },
        { from: "d_active", to: "a_decide", guard: "aktif" },
        { from: "a_decide", to: "d_kep" },

        { from: "d_kep", to: "a_pass", guard: "lulus" },
        { from: "a_pass", to: "d_tes" },
        { from: "d_tes", to: "a_activate", guard: "tidak" },
        { from: "d_tes", to: "d_tescfg", guard: "ya" },
        { from: "d_tescfg", to: "a_activate", guard: "ya" },
        { from: "d_tescfg", to: "a_e2msg", guard: "tidak" },
        { from: "a_e2msg", to: "ff_e2" },
        { from: "a_activate", to: "a_mailpass" },
        { from: "a_mailpass", to: "final_lulus" },

        { from: "d_kep", to: "a_fail", guard: "gagal" },
        { from: "a_fail", to: "a_mailfail" },
        { from: "a_mailfail", to: "final_gagal" },

        { from: "d_kep", to: "a_reserve", guard: "ditangguhkan" },
        { from: "a_reserve", to: "final_tangguh" }
    ]
};

/* ================================ main ================================ */

/* ============================== UC-17 data ============================= */
/* Skrining CV User. Mirrors UC-16 but no E2 (tes-config is HR-only). */
var UC17 = {
    ucId: "UC-17",
    title: "Skrining CV User",
    actorLane: "Kepala Unit / Karyawan",
    maxRow: 7,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_review", kind: "action", lane: "A", col: 0, row: 1, text: "Tinjau CV & isi catatan" },
        { id: "d_active", kind: "decision", lane: "A", col: 0, row: 2, text: "Tahap Aktif / Ditangguhkan?" },
        { id: "a_e1msg", kind: "action", lane: "A", col: 1, row: 2, text: "Pesan: keputusan ditolak" },
        { id: "ff_e1", kind: "flowfinal", lane: "A", col: 2, row: 2 },
        { id: "a_decide", kind: "action", lane: "A", col: 0, row: 3, text: "Pilih keputusan & simpan" },
        { id: "d_kep", kind: "decision", lane: "A", col: 0, row: 4, text: "Keputusan skrining?" },

        { id: "a_pass", kind: "action", lane: "S", col: 0, row: 5, text: "Simpan catatan & peninjau, proses keputusan" },
        { id: "a_mailpass", kind: "action", lane: "S", col: 0, row: 6, text: "Kirim email transisi tahap (UC-28)" },
        { id: "final_lulus", kind: "final", lane: "S", col: 0, row: 7 },

        { id: "a_fail", kind: "action", lane: "S", col: 1, row: 5, text: "Tandai Gagal, hentikan pipeline" },
        { id: "a_mailfail", kind: "action", lane: "S", col: 1, row: 6, text: "Kirim email penolakan (UC-28)" },
        { id: "final_gagal", kind: "final", lane: "S", col: 1, row: 7 },

        { id: "a_reserve", kind: "action", lane: "S", col: 2, row: 5, text: "Tandai Ditangguhkan (UC-29)" },
        { id: "final_tangguh", kind: "final", lane: "S", col: 2, row: 6 }
    ],
    edges: [
        { from: "init", to: "a_review" },
        { from: "a_review", to: "d_active" },
        { from: "d_active", to: "a_e1msg", guard: "sudah diputus" },
        { from: "a_e1msg", to: "ff_e1" },
        { from: "d_active", to: "a_decide", guard: "aktif" },
        { from: "a_decide", to: "d_kep" },
        { from: "d_kep", to: "a_pass", guard: "lulus" },
        { from: "a_pass", to: "a_mailpass" },
        { from: "a_mailpass", to: "final_lulus" },
        { from: "d_kep", to: "a_fail", guard: "gagal" },
        { from: "a_fail", to: "a_mailfail" },
        { from: "a_mailfail", to: "final_gagal" },
        { from: "d_kep", to: "a_reserve", guard: "ditangguhkan" },
        { from: "a_reserve", to: "final_tangguh" }
    ]
};

/* ============================== UC-12 data ============================= */
/* Kelola Lowongan. Save/publish flow + A1 snapshot + E1 validation + E2 delete-guard. */
var UC12 = {
    ucId: "UC-12",
    title: "Kelola Lowongan",
    actorLane: "HR Admin",
    maxRow: 8,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka menu Lowongan" },
        { id: "d_act", kind: "decision", lane: "A", col: 0, row: 2, text: "Aksi?" },

        // simpan/publish branch (lane A col0 -> Sistem col0)
        { id: "a_form", kind: "action", lane: "A", col: 0, row: 3, text: "Isi data lowongan & pilih status" },
        { id: "d_valid", kind: "decision", lane: "S", col: 0, row: 4, text: "Validasi lolos?" },
        { id: "a_err", kind: "action", lane: "S", col: 1, row: 4, text: "Tampilkan error validasi" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 1, row: 5 },
        { id: "a_save", kind: "action", lane: "S", col: 0, row: 5, text: "Simpan lowongan" },
        { id: "a_snap", kind: "action", lane: "S", col: 0, row: 6, text: "Terapkan snapshot alur (UC-13)" },
        { id: "a_ok", kind: "action", lane: "S", col: 0, row: 7, text: "Tampilkan pesan sukses" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 8 },

        // hapus branch (Sistem col2) -- E2 guards running applications
        { id: "d_run", kind: "decision", lane: "S", col: 2, row: 3, text: "Ada lamaran berjalan?" },
        { id: "a_keep", kind: "action", lane: "S", col: 3, row: 3, text: "Jaga konsistensi, batalkan hapus" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 3, row: 4 },
        { id: "a_del", kind: "action", lane: "S", col: 2, row: 4, text: "Hapus lowongan" },
        { id: "final_del", kind: "final", lane: "S", col: 2, row: 5 }
    ],
    edges: [
        { from: "init", to: "a_open" },
        { from: "a_open", to: "d_act" },
        { from: "d_act", to: "a_form", guard: "tambah / ubah" },
        { from: "a_form", to: "d_valid" },
        { from: "d_valid", to: "a_err", guard: "tidak" },
        { from: "a_err", to: "ff_e1" },
        { from: "d_valid", to: "a_save", guard: "ya" },
        { from: "a_save", to: "a_snap" },
        { from: "a_snap", to: "a_ok" },
        { from: "a_ok", to: "final_ok" },
        { from: "d_act", to: "d_run", guard: "hapus" },
        { from: "d_run", to: "a_keep", guard: "ya" },
        { from: "a_keep", to: "ff_e2" },
        { from: "d_run", to: "a_del", guard: "tidak" },
        { from: "a_del", to: "final_del" }
    ]
};

/* ============================== UC-13 data ============================= */
/* Terapkan Template Alur («extend» of UC-12). Small: pick template, snapshot. */
var UC13 = {
    ucId: "UC-13",
    title: "Terapkan Template Alur",
    actorLane: "HR Admin",
    maxRow: 4,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_pick", kind: "action", lane: "A", col: 0, row: 1, text: "Pilih template alur pada form lowongan" },
        { id: "d_sel", kind: "decision", lane: "S", col: 0, row: 2, text: "Template dipilih?" },
        { id: "a_demand", kind: "action", lane: "S", col: 1, row: 2, text: "Tuntut konfigurasi alur valid" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 1, row: 3 },
        { id: "a_copy", kind: "action", lane: "S", col: 0, row: 3, text: "Salin tahapan -> Snapshot + SnapshotStage" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 4 }
    ],
    edges: [
        { from: "init", to: "a_pick" },
        { from: "a_pick", to: "d_sel" },
        { from: "d_sel", to: "a_demand", guard: "tidak" },
        { from: "a_demand", to: "ff_e1" },
        { from: "d_sel", to: "a_copy", guard: "ya" },
        { from: "a_copy", to: "final_ok" }
    ]
};

/* ============================== UC-19 data ============================= */
/* Tinjau Jawaban Tes. Score essays -> save/compute -> decide. E1 skor luar
 * rentang, E2 keputusan sebelum semua dinilai, A1 gagal/ditangguhkan.
 * Tiled sameRow guards (reject col1 / flowfinal col2); decision fan reuses
 * col1/col2 at row7+ (guards end by row5). */
var UC19 = {
    ucId: "UC-19",
    title: "Tinjau Jawaban Tes",
    actorLane: "HR Admin",
    maxRow: 9,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_score", kind: "action", lane: "A", col: 0, row: 1, text: "Beri skor jawaban esai" },
        { id: "d_range", kind: "decision", lane: "S", col: 0, row: 2, text: "Skor dalam rentang poin?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 2, text: "Tampilkan error rentang skor" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "a_savescore", kind: "action", lane: "S", col: 0, row: 3, text: "Simpan skor, tandai ditinjau, hitung total" },
        { id: "a_decide", kind: "action", lane: "A", col: 0, row: 4, text: "Pilih keputusan & simpan" },
        { id: "d_allrev", kind: "decision", lane: "S", col: 0, row: 5, text: "Semua jawaban dinilai?" },
        { id: "a_e2", kind: "action", lane: "S", col: 1, row: 5, text: "Pesan: semua jawaban harus dinilai" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 2, row: 5 },
        { id: "d_kep", kind: "decision", lane: "S", col: 0, row: 6, text: "Keputusan tes?" },
        { id: "a_pass", kind: "action", lane: "S", col: 0, row: 7, text: "Proses keputusan, aktifkan tahap berikut" },
        { id: "a_mailpass", kind: "action", lane: "S", col: 0, row: 8, text: "Kirim email transisi (UC-28)" },
        { id: "final_lulus", kind: "final", lane: "S", col: 0, row: 9 },
        { id: "a_fail", kind: "action", lane: "S", col: 1, row: 7, text: "Tandai Gagal, hentikan pipeline" },
        { id: "a_mailfail", kind: "action", lane: "S", col: 1, row: 8, text: "Kirim email penolakan (UC-28)" },
        { id: "final_gagal", kind: "final", lane: "S", col: 1, row: 9 },
        { id: "a_reserve", kind: "action", lane: "S", col: 2, row: 7, text: "Tandai Ditangguhkan (UC-29)" },
        { id: "final_tangguh", kind: "final", lane: "S", col: 2, row: 8 }
    ],
    edges: [
        { from: "init", to: "a_score" },
        { from: "a_score", to: "d_range" },
        { from: "d_range", to: "a_e1", guard: "di luar rentang" },
        { from: "a_e1", to: "ff_e1" },
        { from: "d_range", to: "a_savescore", guard: "valid" },
        { from: "a_savescore", to: "a_decide" },
        { from: "a_decide", to: "d_allrev" },
        { from: "d_allrev", to: "a_e2", guard: "belum semua" },
        { from: "a_e2", to: "ff_e2" },
        { from: "d_allrev", to: "d_kep", guard: "ya" },
        { from: "d_kep", to: "a_pass", guard: "lulus" },
        { from: "a_pass", to: "a_mailpass" },
        { from: "a_mailpass", to: "final_lulus" },
        { from: "d_kep", to: "a_fail", guard: "gagal" },
        { from: "a_fail", to: "a_mailfail" },
        { from: "a_mailfail", to: "final_gagal" },
        { from: "d_kep", to: "a_reserve", guard: "ditangguhkan" },
        { from: "a_reserve", to: "final_tangguh" }
    ]
};

/* ============================== UC-21 data ============================= */
/* Wawancara User. THREE entry guards (E2 bukan pewawancara 403, E3 sudah
 * diputus, E1 hasil sudah direkam) tiled sameRow col0->col1->col2, then
 * record + 3-way decision fan reusing col1/col2 at row7+. */
var UC21 = {
    ucId: "UC-21",
    title: "Wawancara User",
    actorLane: "Kepala Unit / Karyawan",
    maxRow: 9,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka form keputusan wawancara" },
        { id: "d_auth", kind: "decision", lane: "S", col: 0, row: 2, text: "Pewawancara ditugaskan?" },
        { id: "a_e2", kind: "action", lane: "S", col: 1, row: 2, text: "Tolak akses (403)" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "d_decided", kind: "decision", lane: "S", col: 0, row: 3, text: "Tahap masih dapat diputus?" },
        { id: "a_e3", kind: "action", lane: "S", col: 1, row: 3, text: "Pesan: tahap sudah diputus" },
        { id: "ff_e3", kind: "flowfinal", lane: "S", col: 2, row: 3 },
        { id: "d_recorded", kind: "decision", lane: "S", col: 0, row: 4, text: "Hasil belum direkam?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 4, text: "Pesan: hasil sudah direkam" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 4 },
        { id: "a_fill", kind: "action", lane: "A", col: 0, row: 5, text: "Isi rating, kesiapan, catatan; pilih keputusan; simpan" },
        { id: "d_kep", kind: "decision", lane: "S", col: 0, row: 6, text: "Keputusan wawancara?" },
        { id: "a_pass", kind: "action", lane: "S", col: 0, row: 7, text: "Simpan hasil & proses keputusan" },
        { id: "a_mailpass", kind: "action", lane: "S", col: 0, row: 8, text: "Kirim email transisi (UC-28)" },
        { id: "final_lulus", kind: "final", lane: "S", col: 0, row: 9 },
        { id: "a_fail", kind: "action", lane: "S", col: 1, row: 7, text: "Tandai Gagal, hentikan pipeline" },
        { id: "a_mailfail", kind: "action", lane: "S", col: 1, row: 8, text: "Kirim email penolakan (UC-28)" },
        { id: "final_gagal", kind: "final", lane: "S", col: 1, row: 9 },
        { id: "a_reserve", kind: "action", lane: "S", col: 2, row: 7, text: "Tandai Ditangguhkan (UC-29)" },
        { id: "final_tangguh", kind: "final", lane: "S", col: 2, row: 8 }
    ],
    edges: [
        { from: "init", to: "a_open" },
        { from: "a_open", to: "d_auth" },
        { from: "d_auth", to: "a_e2", guard: "bukan pewawancara" },
        { from: "a_e2", to: "ff_e2" },
        { from: "d_auth", to: "d_decided", guard: "ya" },
        { from: "d_decided", to: "a_e3", guard: "sudah diputus" },
        { from: "a_e3", to: "ff_e3" },
        { from: "d_decided", to: "d_recorded", guard: "dapat diputus" },
        { from: "d_recorded", to: "a_e1", guard: "sudah direkam" },
        { from: "a_e1", to: "ff_e1" },
        { from: "d_recorded", to: "a_fill", guard: "belum direkam" },
        { from: "a_fill", to: "d_kep" },
        { from: "d_kep", to: "a_pass", guard: "lulus" },
        { from: "a_pass", to: "a_mailpass" },
        { from: "a_mailpass", to: "final_lulus" },
        { from: "d_kep", to: "a_fail", guard: "gagal" },
        { from: "a_fail", to: "a_mailfail" },
        { from: "a_mailfail", to: "final_gagal" },
        { from: "d_kep", to: "a_reserve", guard: "ditangguhkan" },
        { from: "a_reserve", to: "final_tangguh" }
    ]
};

/* ============================== UC-22 data ============================= */
/* Wawancara Manajer HR. TWO guards only (E2 sudah diputus, E1 sudah direkam)
 * -- no interviewer-assignment guard. UC-23 clones this with a different
 * actor lane. */
var UC22 = {
    ucId: "UC-22",
    title: "Wawancara Manajer HR",
    actorLane: "Manajer HR",
    maxRow: 8,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka form keputusan wawancara" },
        { id: "d_decided", kind: "decision", lane: "S", col: 0, row: 2, text: "Tahap masih dapat diputus?" },
        { id: "a_e2", kind: "action", lane: "S", col: 1, row: 2, text: "Pesan: tahap sudah diputus" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "d_recorded", kind: "decision", lane: "S", col: 0, row: 3, text: "Hasil belum direkam?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 3, text: "Pesan: hasil sudah direkam" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 3 },
        { id: "a_fill", kind: "action", lane: "A", col: 0, row: 4, text: "Isi penilaian & keputusan; simpan" },
        { id: "d_kep", kind: "decision", lane: "S", col: 0, row: 5, text: "Keputusan wawancara?" },
        { id: "a_pass", kind: "action", lane: "S", col: 0, row: 6, text: "Simpan hasil & proses keputusan" },
        { id: "a_mailpass", kind: "action", lane: "S", col: 0, row: 7, text: "Kirim notifikasi transisi (UC-28)" },
        { id: "final_lulus", kind: "final", lane: "S", col: 0, row: 8 },
        { id: "a_fail", kind: "action", lane: "S", col: 1, row: 6, text: "Tandai Gagal, hentikan pipeline" },
        { id: "a_mailfail", kind: "action", lane: "S", col: 1, row: 7, text: "Kirim email penolakan (UC-28)" },
        { id: "final_gagal", kind: "final", lane: "S", col: 1, row: 8 },
        { id: "a_reserve", kind: "action", lane: "S", col: 2, row: 6, text: "Tandai Ditangguhkan (UC-29)" },
        { id: "final_tangguh", kind: "final", lane: "S", col: 2, row: 7 }
    ],
    edges: [
        { from: "init", to: "a_open" },
        { from: "a_open", to: "d_decided" },
        { from: "d_decided", to: "a_e2", guard: "sudah diputus" },
        { from: "a_e2", to: "ff_e2" },
        { from: "d_decided", to: "d_recorded", guard: "dapat diputus" },
        { from: "d_recorded", to: "a_e1", guard: "sudah direkam" },
        { from: "a_e1", to: "ff_e1" },
        { from: "d_recorded", to: "a_fill", guard: "belum direkam" },
        { from: "a_fill", to: "d_kep" },
        { from: "d_kep", to: "a_pass", guard: "lulus" },
        { from: "a_pass", to: "a_mailpass" },
        { from: "a_mailpass", to: "final_lulus" },
        { from: "d_kep", to: "a_fail", guard: "gagal" },
        { from: "a_fail", to: "a_mailfail" },
        { from: "a_mailfail", to: "final_gagal" },
        { from: "d_kep", to: "a_reserve", guard: "ditangguhkan" },
        { from: "a_reserve", to: "final_tangguh" }
    ]
};

/* ============================== UC-26 data ============================= */
/* Kirim Surat Penawaran. Linear: guard sudah-dikirim -> save+links -> send
 * email -> guard email-ok -> mark sent_at. E1 sudah dikirim, E2 gagal kirim. */
var UC26 = {
    ucId: "UC-26",
    title: "Kirim Surat Penawaran",
    actorLane: "HR Admin",
    maxRow: 7,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_fill", kind: "action", lane: "A", col: 0, row: 1, text: "Isi detail penawaran & kirim" },
        { id: "d_sent", kind: "decision", lane: "S", col: 0, row: 2, text: "Penawaran sudah dikirim?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 2, text: "Pesan: sudah pernah dikirim" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "a_save", kind: "action", lane: "S", col: 0, row: 3, text: "Simpan penawaran (pending), buat tautan terima/tolak (7 hari)" },
        { id: "a_mail", kind: "action", lane: "S", col: 0, row: 4, text: "Kirim email penawaran (UC-28)" },
        { id: "d_mailok", kind: "decision", lane: "S", col: 0, row: 5, text: "Email terkirim?" },
        { id: "a_e2", kind: "action", lane: "S", col: 1, row: 5, text: "Pesan: gagal kirim, coba lagi" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 2, row: 5 },
        { id: "a_mark", kind: "action", lane: "S", col: 0, row: 6, text: "Tandai sent_at" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 7 }
    ],
    edges: [
        { from: "init", to: "a_fill" },
        { from: "a_fill", to: "d_sent" },
        { from: "d_sent", to: "a_e1", guard: "sudah" },
        { from: "a_e1", to: "ff_e1" },
        { from: "d_sent", to: "a_save", guard: "belum" },
        { from: "a_save", to: "a_mail" },
        { from: "a_mail", to: "d_mailok" },
        { from: "d_mailok", to: "a_e2", guard: "gagal" },
        { from: "a_e2", to: "ff_e2" },
        { from: "d_mailok", to: "a_mark", guard: "terkirim" },
        { from: "a_mark", to: "final_ok" }
    ]
};

/* ============================== UC-31 data ============================= */
/* Lamar Lowongan (wizard 8 langkah). Per advisor: NO loop-back edge (every
 * other UC models validation-fail as a flowfinal); the per-step iteration
 * belongs to UC-32. E3 lowongan tak tersedia, E1 validasi langkah gagal,
 * E2 email sudah melamar. All down + sameRow geometry. */
var UC31 = {
    ucId: "UC-31",
    title: "Lamar Lowongan",
    actorLane: "Kandidat",
    maxRow: 8,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka halaman lamar" },
        { id: "d_avail", kind: "decision", lane: "S", col: 0, row: 2, text: "Lowongan tersedia & dalam tenggat?" },
        { id: "a_e3", kind: "action", lane: "S", col: 1, row: 2, text: "Tampilkan 404" },
        { id: "ff_e3", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "a_fill", kind: "action", lane: "A", col: 0, row: 3, text: "Isi tiap langkah (1..8) & unggah CV/STR" },
        { id: "d_valid", kind: "decision", lane: "S", col: 0, row: 4, text: "Validasi langkah lolos? (UC-32)" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 4, text: "Tampilkan error per-field (422)" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 4 },
        { id: "d_email", kind: "decision", lane: "S", col: 0, row: 5, text: "Email belum melamar lowongan ini?" },
        { id: "a_e2", kind: "action", lane: "S", col: 1, row: 5, text: "Pesan: sudah pernah melamar" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 2, row: 5 },
        { id: "a_save", kind: "action", lane: "S", col: 0, row: 6, text: "Simpan kandidat, lamaran & tahapan dari snapshot; buat token" },
        { id: "a_redir", kind: "action", lane: "S", col: 0, row: 7, text: "Kirim tautan status & alihkan ke konfirmasi" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 8 }
    ],
    edges: [
        { from: "init", to: "a_open" },
        { from: "a_open", to: "d_avail" },
        { from: "d_avail", to: "a_e3", guard: "tidak" },
        { from: "a_e3", to: "ff_e3" },
        { from: "d_avail", to: "a_fill", guard: "ya" },
        { from: "a_fill", to: "d_valid" },
        { from: "d_valid", to: "a_e1", guard: "gagal" },
        { from: "a_e1", to: "ff_e1" },
        { from: "d_valid", to: "d_email", guard: "lolos" },
        { from: "d_email", to: "a_e2", guard: "sudah melamar" },
        { from: "a_e2", to: "ff_e2" },
        { from: "d_email", to: "a_save", guard: "belum" },
        { from: "a_save", to: "a_redir" },
        { from: "a_redir", to: "final_ok" }
    ]
};

/* ============================== UC-33 data ============================= */
/* Kerjakan Tes Kompetensi (kandidat, tautan tertoken). Two tiled guards
 * (E1 token invalid, E2 sudah dikerjakan) then show -> work -> save.
 * UC-34 (DiSC) / UC-35 (MBTI) clone this with text overrides. */
var UC33 = {
    ucId: "UC-33",
    title: "Kerjakan Tes Kompetensi",
    actorLane: "Kandidat",
    maxRow: 7,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka tautan tes" },
        { id: "d_token", kind: "decision", lane: "S", col: 0, row: 2, text: "Token valid?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 2, text: "Tolak akses" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "d_done", kind: "decision", lane: "S", col: 0, row: 3, text: "Tes belum dikerjakan?" },
        { id: "a_e2", kind: "action", lane: "S", col: 1, row: 3, text: "Tampilkan status sudah dikirim" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 2, row: 3 },
        { id: "a_show", kind: "action", lane: "S", col: 0, row: 4, text: "Tampilkan soal dari snapshot tes" },
        { id: "a_work", kind: "action", lane: "A", col: 0, row: 5, text: "Kerjakan & kirim jawaban" },
        { id: "a_save", kind: "action", lane: "S", col: 0, row: 6, text: "Simpan jawaban pada submission" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 7 }
    ],
    edges: [
        { from: "init", to: "a_open" },
        { from: "a_open", to: "d_token" },
        { from: "d_token", to: "a_e1", guard: "tidak valid" },
        { from: "a_e1", to: "ff_e1" },
        { from: "d_token", to: "d_done", guard: "valid" },
        { from: "d_done", to: "a_e2", guard: "sudah" },
        { from: "a_e2", to: "ff_e2" },
        { from: "d_done", to: "a_show", guard: "belum" },
        { from: "a_show", to: "a_work" },
        { from: "a_work", to: "a_save" },
        { from: "a_save", to: "final_ok" }
    ]
};

/* ===================== clone helper (ES3 deep copy) ==================== */
/* Build a structural twin of a base model with a new id/title/actor lane and
 * optional per-node text overrides ({nodeId: "new text"}). */
function cloneModel(base, ucId, title, actorLane, textOverrides) {
    var nodes = [], i;
    for (i = 0; i < base.nodes.length; i++) {
        var n = base.nodes[i];
        var t = n.text;
        if (textOverrides && textOverrides[n.id]) { t = textOverrides[n.id]; }
        nodes.push({ id: n.id, kind: n.kind, lane: n.lane, col: n.col, row: n.row, text: t });
    }
    var edges = [], j;
    for (j = 0; j < base.edges.length; j++) {
        var e = base.edges[j];
        edges.push({ from: e.from, to: e.to, guard: e.guard });
    }
    return { ucId: ucId, title: title, actorLane: actorLane, maxRow: base.maxRow,
             nodes: nodes, edges: edges };
}

var UC23 = cloneModel(UC22, "UC-23", "Wawancara Direktur", "Direktur", null);
var UC34 = cloneModel(UC33, "UC-34", "Kerjakan Tes DiSC", "Kandidat", {
    a_show: "Tampilkan pernyataan tes DiSC",
    a_work: "Kerjakan & kirim tes DiSC",
    a_save: "Simpan jawaban & hitung hasil DiSC"
});
var UC35 = cloneModel(UC33, "UC-35", "Kerjakan Tes MBTI", "Kandidat", {
    a_show: "Tampilkan pernyataan tes MBTI",
    a_work: "Kerjakan & kirim tes MBTI",
    a_save: "Simpan jawaban & hitung tipe MBTI"
});

/* ====================== LINEAR BATCH 3a: UC-01..11 ===================== */
/* Auth/general + CRUD master. Spine-only; tiled-sameRow guards. CRUD-with-
 * delete (05/07) mirror UC-12's two-top-branch layout (E1 flowfinal stays in
 * col1 to leave col2/col3 for the hapus branch). */

/* UC-01 Login. Guards: rate-limit, kredensial, akun aktif. */
var UC01 = {
    ucId: "UC-01", title: "Login", actorLane: "Pengguna Internal", maxRow: 7,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_enter", kind: "action", lane: "A", col: 0, row: 1, text: "Masukkan email & kata sandi, tekan masuk" },
        { id: "d_rate", kind: "decision", lane: "S", col: 0, row: 2, text: "Dalam batas percobaan?" },
        { id: "a_e3", kind: "action", lane: "S", col: 1, row: 2, text: "Batasi sementara (rate limit)" },
        { id: "ff_e3", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "d_cred", kind: "decision", lane: "S", col: 0, row: 3, text: "Kredensial benar?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 3, text: "Tampilkan pesan gagal" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 3 },
        { id: "d_active", kind: "decision", lane: "S", col: 0, row: 4, text: "Akun aktif?" },
        { id: "a_e2", kind: "action", lane: "S", col: 1, row: 4, text: "Tolak masuk (akun nonaktif)" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 2, row: 4 },
        { id: "a_session", kind: "action", lane: "S", col: 0, row: 5, text: "Buat sesi terotorisasi" },
        { id: "a_redirect", kind: "action", lane: "S", col: 0, row: 6, text: "Alihkan ke dashboard sesuai peran" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 7 }
    ],
    edges: [
        { from: "init", to: "a_enter" }, { from: "a_enter", to: "d_rate" },
        { from: "d_rate", to: "a_e3", guard: "melebihi" }, { from: "a_e3", to: "ff_e3" },
        { from: "d_rate", to: "d_cred", guard: "ya" },
        { from: "d_cred", to: "a_e1", guard: "salah" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_cred", to: "d_active", guard: "benar" },
        { from: "d_active", to: "a_e2", guard: "nonaktif" }, { from: "a_e2", to: "ff_e2" },
        { from: "d_active", to: "a_session", guard: "aktif" },
        { from: "a_session", to: "a_redirect" }, { from: "a_redirect", to: "final_ok" }
    ]
};

/* UC-02 Ubah Password. Guards: throttle, kata sandi lama, aturan baru. */
var UC02 = {
    ucId: "UC-02", title: "Ubah Password", actorLane: "Pengguna Internal", maxRow: 7,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_fill", kind: "action", lane: "A", col: 0, row: 1, text: "Isi kata sandi lama, baru & konfirmasi" },
        { id: "d_throttle", kind: "decision", lane: "S", col: 0, row: 2, text: "Dalam batas percobaan?" },
        { id: "a_e3", kind: "action", lane: "S", col: 1, row: 2, text: "Batasi (throttle 5/menit)" },
        { id: "ff_e3", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "d_old", kind: "decision", lane: "S", col: 0, row: 3, text: "Kata sandi lama benar?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 3, text: "Tolak perubahan" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 3 },
        { id: "d_rules", kind: "decision", lane: "S", col: 0, row: 4, text: "Kata sandi baru valid & cocok?" },
        { id: "a_e2", kind: "action", lane: "S", col: 1, row: 4, text: "Tampilkan error validasi" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 2, row: 4 },
        { id: "a_save", kind: "action", lane: "S", col: 0, row: 5, text: "Simpan kata sandi ter-hash" },
        { id: "a_ok", kind: "action", lane: "S", col: 0, row: 6, text: "Tampilkan pesan sukses" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 7 }
    ],
    edges: [
        { from: "init", to: "a_fill" }, { from: "a_fill", to: "d_throttle" },
        { from: "d_throttle", to: "a_e3", guard: "melebihi" }, { from: "a_e3", to: "ff_e3" },
        { from: "d_throttle", to: "d_old", guard: "ya" },
        { from: "d_old", to: "a_e1", guard: "salah" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_old", to: "d_rules", guard: "benar" },
        { from: "d_rules", to: "a_e2", guard: "tidak" }, { from: "a_e2", to: "ff_e2" },
        { from: "d_rules", to: "a_save", guard: "ya" },
        { from: "a_save", to: "a_ok" }, { from: "a_ok", to: "final_ok" }
    ]
};

/* UC-03 Lihat Notifikasi. A1 kosong (local final). */
var UC03 = {
    ucId: "UC-03", title: "Lihat Notifikasi", actorLane: "Pengguna Internal", maxRow: 4,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka halaman notifikasi" },
        { id: "d_any", kind: "decision", lane: "S", col: 0, row: 2, text: "Ada notifikasi?" },
        { id: "a_empty", kind: "action", lane: "S", col: 1, row: 2, text: "Tampilkan keadaan kosong" },
        { id: "final_empty", kind: "final", lane: "S", col: 2, row: 2 },
        { id: "a_list", kind: "action", lane: "S", col: 0, row: 3, text: "Tampilkan daftar terurut terbaru" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 4 }
    ],
    edges: [
        { from: "init", to: "a_open" }, { from: "a_open", to: "d_any" },
        { from: "d_any", to: "a_empty", guard: "tidak" }, { from: "a_empty", to: "final_empty" },
        { from: "d_any", to: "a_list", guard: "ya" }, { from: "a_list", to: "final_ok" }
    ]
};

/* UC-04 Lihat Profil Sendiri. E1 akun belum tertaut. */
var UC04 = {
    ucId: "UC-04", title: "Lihat Profil Sendiri", actorLane: "Karyawan", maxRow: 4,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka halaman profil" },
        { id: "d_linked", kind: "decision", lane: "S", col: 0, row: 2, text: "Akun tertaut data karyawan?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 2, text: "Tampilkan kosong / keterbatasan akses" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "a_show", kind: "action", lane: "S", col: 0, row: 3, text: "Tampilkan data karyawan miliknya" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 4 }
    ],
    edges: [
        { from: "init", to: "a_open" }, { from: "a_open", to: "d_linked" },
        { from: "d_linked", to: "a_e1", guard: "tidak" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_linked", to: "a_show", guard: "ya" }, { from: "a_show", to: "final_ok" }
    ]
};

/* UC-05 Kelola Unit. CRUD + delete-guard (mirrors UC-12 two-branch layout).
 * Base for UC-07 (Karyawan) clone. */
var UC05 = {
    ucId: "UC-05", title: "Kelola Unit", actorLane: "HR Admin", maxRow: 7,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka menu Unit" },
        { id: "d_act", kind: "decision", lane: "A", col: 0, row: 2, text: "Aksi?" },
        { id: "a_form", kind: "action", lane: "A", col: 0, row: 3, text: "Isi data unit" },
        { id: "d_valid", kind: "decision", lane: "S", col: 0, row: 4, text: "Validasi lolos?" },
        { id: "a_err", kind: "action", lane: "S", col: 1, row: 4, text: "Tampilkan error per-field" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 1, row: 5 },
        { id: "a_save", kind: "action", lane: "S", col: 0, row: 5, text: "Simpan perubahan" },
        { id: "a_ok", kind: "action", lane: "S", col: 0, row: 6, text: "Tampilkan daftar terbaru & pesan sukses" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 7 },
        { id: "d_used", kind: "decision", lane: "S", col: 2, row: 3, text: "Unit masih dipakai?" },
        { id: "a_keep", kind: "action", lane: "S", col: 3, row: 3, text: "Tolak penghapusan, jaga konsistensi" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 3, row: 4 },
        { id: "a_del", kind: "action", lane: "S", col: 2, row: 4, text: "Hapus unit" },
        { id: "final_del", kind: "final", lane: "S", col: 2, row: 5 }
    ],
    edges: [
        { from: "init", to: "a_open" }, { from: "a_open", to: "d_act" },
        { from: "d_act", to: "a_form", guard: "tambah / ubah" },
        { from: "a_form", to: "d_valid" },
        { from: "d_valid", to: "a_err", guard: "tidak" }, { from: "a_err", to: "ff_e1" },
        { from: "d_valid", to: "a_save", guard: "ya" },
        { from: "a_save", to: "a_ok" }, { from: "a_ok", to: "final_ok" },
        { from: "d_act", to: "d_used", guard: "hapus" },
        { from: "d_used", to: "a_keep", guard: "ya" }, { from: "a_keep", to: "ff_e2" },
        { from: "d_used", to: "a_del", guard: "tidak" }, { from: "a_del", to: "final_del" }
    ]
};

/* UC-06 Kelola Akun. CRUD + A1 toggle aktif (no delete). */
var UC06 = {
    ucId: "UC-06", title: "Kelola Akun", actorLane: "HR Admin", maxRow: 7,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka menu Akun" },
        { id: "d_act", kind: "decision", lane: "A", col: 0, row: 2, text: "Aksi?" },
        { id: "a_form", kind: "action", lane: "A", col: 0, row: 3, text: "Buat/sunting akun, pilih peran, tautkan karyawan" },
        { id: "d_valid", kind: "decision", lane: "S", col: 0, row: 4, text: "Validasi lolos?" },
        { id: "a_err", kind: "action", lane: "S", col: 1, row: 4, text: "Tampilkan error" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 1, row: 5 },
        { id: "a_save", kind: "action", lane: "S", col: 0, row: 5, text: "Simpan akun" },
        { id: "a_ok", kind: "action", lane: "S", col: 0, row: 6, text: "Tampilkan pesan sukses" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 7 },
        { id: "a_toggle", kind: "action", lane: "S", col: 2, row: 3, text: "Perbarui status aktif/nonaktif" },
        { id: "final_toggle", kind: "final", lane: "S", col: 2, row: 4 }
    ],
    edges: [
        { from: "init", to: "a_open" }, { from: "a_open", to: "d_act" },
        { from: "d_act", to: "a_form", guard: "buat / sunting" },
        { from: "a_form", to: "d_valid" },
        { from: "d_valid", to: "a_err", guard: "tidak" }, { from: "a_err", to: "ff_e1" },
        { from: "d_valid", to: "a_save", guard: "ya" },
        { from: "a_save", to: "a_ok" }, { from: "a_ok", to: "final_ok" },
        { from: "d_act", to: "a_toggle", guard: "toggle aktif" },
        { from: "a_toggle", to: "final_toggle" }
    ]
};

/* UC-08 Kelola Template Alur. Guards: tahap terkunci awal/akhir, validasi. */
var UC08 = {
    ucId: "UC-08", title: "Kelola Template Alur", actorLane: "HR Admin", maxRow: 6,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_compose", kind: "action", lane: "A", col: 0, row: 1, text: "Susun template: pilih & urutkan tahapan" },
        { id: "d_locked", kind: "decision", lane: "S", col: 0, row: 2, text: "Tahap awal/akhir terkunci dipertahankan?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 2, text: "Pertahankan Lamaran awal & Onboarding akhir" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "d_valid", kind: "decision", lane: "S", col: 0, row: 3, text: "Validasi lolos?" },
        { id: "a_e2", kind: "action", lane: "S", col: 1, row: 3, text: "Tampilkan error" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 2, row: 3 },
        { id: "a_save", kind: "action", lane: "S", col: 0, row: 4, text: "Simpan template" },
        { id: "a_ok", kind: "action", lane: "S", col: 0, row: 5, text: "Tampilkan pesan sukses" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 6 }
    ],
    edges: [
        { from: "init", to: "a_compose" }, { from: "a_compose", to: "d_locked" },
        { from: "d_locked", to: "a_e1", guard: "dilanggar" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_locked", to: "d_valid", guard: "dipertahankan" },
        { from: "d_valid", to: "a_e2", guard: "tidak" }, { from: "a_e2", to: "ff_e2" },
        { from: "d_valid", to: "a_save", guard: "ya" },
        { from: "a_save", to: "a_ok" }, { from: "a_ok", to: "final_ok" }
    ]
};

/* UC-09 Kelola Template Wawancara. Simple CRUD, E1 validate.
 * Base for UC-10 (Bank Soal) & UC-11 (Template Email) clones. */
var UC09 = {
    ucId: "UC-09", title: "Kelola Template Wawancara", actorLane: "HR Admin", maxRow: 5,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_edit", kind: "action", lane: "A", col: 0, row: 1, text: "Tambah/sunting item template (kriteria/pertanyaan)" },
        { id: "d_valid", kind: "decision", lane: "S", col: 0, row: 2, text: "Validasi lolos?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 2, text: "Tampilkan error per-field" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "a_save", kind: "action", lane: "S", col: 0, row: 3, text: "Simpan template" },
        { id: "a_ok", kind: "action", lane: "S", col: 0, row: 4, text: "Tampilkan pesan sukses" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 5 }
    ],
    edges: [
        { from: "init", to: "a_edit" }, { from: "a_edit", to: "d_valid" },
        { from: "d_valid", to: "a_e1", guard: "tidak" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_valid", to: "a_save", guard: "ya" },
        { from: "a_save", to: "a_ok" }, { from: "a_ok", to: "final_ok" }
    ]
};

var UC07 = cloneModel(UC05, "UC-07", "Kelola Karyawan", "HR Admin", {
    a_open: "Buka menu Karyawan",
    a_form: "Isi data karyawan (unit, jabatan)",
    a_ok: "Tampilkan daftar terbaru & pesan sukses",
    d_used: "Karyawan tertaut akun?",
    a_keep: "Jaga konsistensi relasi, batalkan hapus",
    a_del: "Hapus karyawan"
});
var UC10 = cloneModel(UC09, "UC-10", "Kelola Bank Soal", "HR Admin", {
    a_edit: "Tambah/sunting soal beserta opsi & poin",
    a_save: "Simpan template bank soal"
});
var UC11 = cloneModel(UC09, "UC-11", "Kelola Template Email", "HR Admin", {
    a_edit: "Pilih template, sunting subjek & isi",
    d_valid: "Placeholder valid?",
    a_save: "Simpan template email"
});

/* ====================== LINEAR BATCH 3b: final 13 ===================== */
/* Views, MCU/onboard/offer, shared. Spine-only; tiled-sameRow guards;
 * UC-25/UC-36 add a decision fan at the bottom (UC-19 pattern). */

/* UC-14 Lihat Pipeline. A2 kosong (local final), A1 buka detail (UC-15). */
var UC14 = {
    ucId: "UC-14", title: "Lihat Pipeline", actorLane: "HR Admin", maxRow: 5,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka halaman pipeline lowongan" },
        { id: "d_any", kind: "decision", lane: "S", col: 0, row: 2, text: "Ada kandidat?" },
        { id: "a_empty", kind: "action", lane: "S", col: 1, row: 2, text: "Tampilkan keadaan kosong" },
        { id: "final_empty", kind: "final", lane: "S", col: 2, row: 2 },
        { id: "a_board", kind: "action", lane: "S", col: 0, row: 3, text: "Tampilkan kandidat per tahap & status" },
        { id: "d_detail", kind: "decision", lane: "S", col: 0, row: 4, text: "Buka detail kandidat?" },
        { id: "a_detail", kind: "action", lane: "S", col: 1, row: 4, text: "Tampilkan profil & riwayat (UC-15)" },
        { id: "final_detail", kind: "final", lane: "S", col: 2, row: 4 },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 5 }
    ],
    edges: [
        { from: "init", to: "a_open" }, { from: "a_open", to: "d_any" },
        { from: "d_any", to: "a_empty", guard: "tidak" }, { from: "a_empty", to: "final_empty" },
        { from: "d_any", to: "a_board", guard: "ya" }, { from: "a_board", to: "d_detail" },
        { from: "d_detail", to: "a_detail", guard: "ya" }, { from: "a_detail", to: "final_detail" },
        { from: "d_detail", to: "final_ok", guard: "tidak" }
    ]
};

/* UC-15 Lihat Detail Kandidat. E1 bukan milik lowongan (404), A1 ekspor PDF. */
var UC15 = {
    ucId: "UC-15", title: "Lihat Detail Kandidat", actorLane: "HR Admin", maxRow: 5,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_select", kind: "action", lane: "A", col: 0, row: 1, text: "Pilih kandidat dari pipeline" },
        { id: "d_owned", kind: "decision", lane: "S", col: 0, row: 2, text: "Lamaran milik lowongan?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 2, text: "Tampilkan 404" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "a_show", kind: "action", lane: "S", col: 0, row: 3, text: "Tampilkan profil, berkas & hasil tahap" },
        { id: "d_pdf", kind: "decision", lane: "S", col: 0, row: 4, text: "Ekspor PDF?" },
        { id: "a_pdf", kind: "action", lane: "S", col: 1, row: 4, text: "Hasilkan PDF profil kandidat" },
        { id: "final_pdf", kind: "final", lane: "S", col: 2, row: 4 },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 5 }
    ],
    edges: [
        { from: "init", to: "a_select" }, { from: "a_select", to: "d_owned" },
        { from: "d_owned", to: "a_e1", guard: "tidak" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_owned", to: "a_show", guard: "ya" }, { from: "a_show", to: "d_pdf" },
        { from: "d_pdf", to: "a_pdf", guard: "ya" }, { from: "a_pdf", to: "final_pdf" },
        { from: "d_pdf", to: "final_ok", guard: "tidak" }
    ]
};

/* UC-18 Kelola Tes Kompetensi. E1 validate; save + snapshot. */
var UC18 = {
    ucId: "UC-18", title: "Kelola Tes Kompetensi", actorLane: "HR Admin", maxRow: 5,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_compose", kind: "action", lane: "A", col: 0, row: 1, text: "Susun soal & parameter tes" },
        { id: "d_valid", kind: "decision", lane: "S", col: 0, row: 2, text: "Validasi lolos?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 2, text: "Tampilkan error" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "a_save", kind: "action", lane: "S", col: 0, row: 3, text: "Simpan konfigurasi & buat snapshot tes" },
        { id: "a_ok", kind: "action", lane: "S", col: 0, row: 4, text: "Tampilkan pesan sukses" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 5 }
    ],
    edges: [
        { from: "init", to: "a_compose" }, { from: "a_compose", to: "d_valid" },
        { from: "d_valid", to: "a_e1", guard: "tidak" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_valid", to: "a_save", guard: "ya" },
        { from: "a_save", to: "a_ok" }, { from: "a_ok", to: "final_ok" }
    ]
};

/* UC-20 Lihat Hasil DiSC/MBTI. A1 belum dikerjakan (local final). */
var UC20 = {
    ucId: "UC-20", title: "Lihat Hasil DiSC/MBTI", actorLane: "HR Admin", maxRow: 4,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka detail kandidat" },
        { id: "d_done", kind: "decision", lane: "S", col: 0, row: 2, text: "Tes kepribadian sudah dikerjakan?" },
        { id: "a_na", kind: "action", lane: "S", col: 1, row: 2, text: "Tampilkan status belum tersedia" },
        { id: "final_na", kind: "final", lane: "S", col: 2, row: 2 },
        { id: "a_show", kind: "action", lane: "S", col: 0, row: 3, text: "Tampilkan hasil DiSC/MBTI terhitung" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 4 }
    ],
    edges: [
        { from: "init", to: "a_open" }, { from: "a_open", to: "d_done" },
        { from: "d_done", to: "a_na", guard: "belum" }, { from: "a_na", to: "final_na" },
        { from: "d_done", to: "a_show", guard: "sudah" }, { from: "a_show", to: "final_ok" }
    ]
};

/* UC-24 Jadwalkan MCU. E1 validate. */
var UC24 = {
    ucId: "UC-24", title: "Jadwalkan MCU", actorLane: "HR Admin", maxRow: 5,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_fill", kind: "action", lane: "A", col: 0, row: 1, text: "Isi jadwal & lokasi MCU" },
        { id: "d_valid", kind: "decision", lane: "S", col: 0, row: 2, text: "Validasi lolos?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 2, text: "Tampilkan error" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "a_save", kind: "action", lane: "S", col: 0, row: 3, text: "Simpan jadwal pada tahap MCU" },
        { id: "a_ok", kind: "action", lane: "S", col: 0, row: 4, text: "Tampilkan pesan sukses" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 5 }
    ],
    edges: [
        { from: "init", to: "a_fill" }, { from: "a_fill", to: "d_valid" },
        { from: "d_valid", to: "a_e1", guard: "tidak" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_valid", to: "a_save", guard: "ya" },
        { from: "a_save", to: "a_ok" }, { from: "a_ok", to: "final_ok" }
    ]
};

/* UC-25 Keputusan MCU. Guards E1 sudah diproses, E2 hasil direkam; E3 rollback
 * berkas on save error; A1/A2 fail/reserve via decision fan. */
var UC25 = {
    ucId: "UC-25", title: "Keputusan MCU", actorLane: "HR Admin", maxRow: 9,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka form keputusan MCU" },
        { id: "d_processed", kind: "decision", lane: "S", col: 0, row: 2, text: "MCU belum diproses?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 2, text: "Pesan: MCU sudah selesai diproses" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "d_recorded", kind: "decision", lane: "S", col: 0, row: 3, text: "Hasil belum direkam?" },
        { id: "a_e2", kind: "action", lane: "S", col: 1, row: 3, text: "Pesan: hasil sudah direkam" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 2, row: 3 },
        { id: "a_fill", kind: "action", lane: "A", col: 0, row: 4, text: "Pilih keputusan, unggah dokumen, isi catatan; simpan" },
        { id: "d_save", kind: "decision", lane: "S", col: 0, row: 5, text: "Simpan berhasil?" },
        { id: "a_e3", kind: "action", lane: "S", col: 1, row: 5, text: "Hapus dokumen terunggah (rollback)" },
        { id: "ff_e3", kind: "flowfinal", lane: "S", col: 2, row: 5 },
        { id: "d_kep", kind: "decision", lane: "S", col: 0, row: 6, text: "Keputusan MCU?" },
        { id: "a_pass", kind: "action", lane: "S", col: 0, row: 7, text: "Proses (advance) ke onboarding" },
        { id: "a_mailpass", kind: "action", lane: "S", col: 0, row: 8, text: "Kirim email transisi (UC-28)" },
        { id: "final_lulus", kind: "final", lane: "S", col: 0, row: 9 },
        { id: "a_fail", kind: "action", lane: "S", col: 1, row: 7, text: "Tandai Gagal, kandidat ditolak" },
        { id: "a_mailfail", kind: "action", lane: "S", col: 1, row: 8, text: "Kirim email penolakan (UC-28)" },
        { id: "final_gagal", kind: "final", lane: "S", col: 1, row: 9 },
        { id: "a_reserve", kind: "action", lane: "S", col: 2, row: 7, text: "Tandai Ditangguhkan (UC-29)" },
        { id: "final_tangguh", kind: "final", lane: "S", col: 2, row: 8 }
    ],
    edges: [
        { from: "init", to: "a_open" }, { from: "a_open", to: "d_processed" },
        { from: "d_processed", to: "a_e1", guard: "sudah" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_processed", to: "d_recorded", guard: "belum" },
        { from: "d_recorded", to: "a_e2", guard: "sudah direkam" }, { from: "a_e2", to: "ff_e2" },
        { from: "d_recorded", to: "a_fill", guard: "belum" },
        { from: "a_fill", to: "d_save" },
        { from: "d_save", to: "a_e3", guard: "galat" }, { from: "a_e3", to: "ff_e3" },
        { from: "d_save", to: "d_kep", guard: "berhasil" },
        { from: "d_kep", to: "a_pass", guard: "lulus" },
        { from: "a_pass", to: "a_mailpass" }, { from: "a_mailpass", to: "final_lulus" },
        { from: "d_kep", to: "a_fail", guard: "tidak lulus" },
        { from: "a_fail", to: "a_mailfail" }, { from: "a_mailfail", to: "final_gagal" },
        { from: "d_kep", to: "a_reserve", guard: "ditangguhkan" },
        { from: "a_reserve", to: "final_tangguh" }
    ]
};

/* UC-27 Proses Onboarding. E1 sudah selesai; send invite -> mark complete. */
var UC27 = {
    ucId: "UC-27", title: "Proses Onboarding", actorLane: "HR Admin", maxRow: 6,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_fill", kind: "action", lane: "A", col: 0, row: 1, text: "Isi tanggal bergabung & catatan, kirim undangan" },
        { id: "d_done", kind: "decision", lane: "S", col: 0, row: 2, text: "Onboarding belum selesai?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 2, text: "Pesan: onboarding sudah selesai" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "a_invite", kind: "action", lane: "S", col: 0, row: 3, text: "Simpan data onboarding, kirim email undangan (UC-28)" },
        { id: "a_mark", kind: "action", lane: "A", col: 0, row: 4, text: "Tandai onboarding selesai" },
        { id: "a_close", kind: "action", lane: "S", col: 0, row: 5, text: "Selesaikan tahap (advance) & tutup pipeline" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 6 }
    ],
    edges: [
        { from: "init", to: "a_fill" }, { from: "a_fill", to: "d_done" },
        { from: "d_done", to: "a_e1", guard: "sudah selesai" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_done", to: "a_invite", guard: "belum" },
        { from: "a_invite", to: "a_mark" }, { from: "a_mark", to: "a_close" },
        { from: "a_close", to: "final_ok" }
    ]
};

/* UC-28 Kirim Notifikasi Email (use case bersama). E1 gagal best-effort. */
var UC28 = {
    ucId: "UC-28", title: "Kirim Notifikasi Email", actorLane: "Sistem Pemanggil", maxRow: 5,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_trigger", kind: "action", lane: "A", col: 0, row: 1, text: "Picu pengiriman dgn jenis & data konteks" },
        { id: "a_compose", kind: "action", lane: "S", col: 0, row: 2, text: "Susun email dari template (UC-11)" },
        { id: "d_sent", kind: "decision", lane: "S", col: 0, row: 3, text: "Pengiriman berhasil?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 3, text: "Laporkan kegagalan (report), tahap utama tetap lanjut" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 3 },
        { id: "a_done", kind: "action", lane: "S", col: 0, row: 4, text: "Email terkirim ke kandidat" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 5 }
    ],
    edges: [
        { from: "init", to: "a_trigger" }, { from: "a_trigger", to: "a_compose" },
        { from: "a_compose", to: "d_sent" },
        { from: "d_sent", to: "a_e1", guard: "gagal" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_sent", to: "a_done", guard: "berhasil" }, { from: "a_done", to: "final_ok" }
    ]
};

/* UC-29 Tandai Reserved («extend»). E1 tidak ada tahap aktif. */
var UC29 = {
    ucId: "UC-29", title: "Tandai Reserved", actorLane: "HR Admin / Kepala Unit", maxRow: 4,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_pick", kind: "action", lane: "A", col: 0, row: 1, text: "Pilih keputusan Ditangguhkan" },
        { id: "d_active", kind: "decision", lane: "S", col: 0, row: 2, text: "Ada tahap aktif?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 2, text: "Pesan: tidak ada tahap aktif" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "a_mark", kind: "action", lane: "S", col: 0, row: 3, text: "Tandai tahap aktif menjadi Ditangguhkan" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 4 }
    ],
    edges: [
        { from: "init", to: "a_pick" }, { from: "a_pick", to: "d_active" },
        { from: "d_active", to: "a_e1", guard: "tidak ada" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_active", to: "a_mark", guard: "ada" }, { from: "a_mark", to: "final_ok" }
    ]
};

/* UC-30 Lihat Lowongan (portal). E1 tidak Published/kedaluwarsa (404). */
var UC30 = {
    ucId: "UC-30", title: "Lihat Lowongan", actorLane: "Kandidat", maxRow: 4,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka daftar/detail lowongan" },
        { id: "d_pub", kind: "decision", lane: "S", col: 0, row: 2, text: "Published & dalam tenggat?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 2, text: "Tidak ditampilkan / 404" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "a_show", kind: "action", lane: "S", col: 0, row: 3, text: "Tampilkan lowongan Published & detail" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 4 }
    ],
    edges: [
        { from: "init", to: "a_open" }, { from: "a_open", to: "d_pub" },
        { from: "d_pub", to: "a_e1", guard: "tidak" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_pub", to: "a_show", guard: "ya" }, { from: "a_show", to: "final_ok" }
    ]
};

/* UC-32 Isi Data Pribadi (per-step validate, no persist). E2 langkah luar 1-8,
 * E3 email langkah 1 sudah melamar, E1 validasi langkah gagal (422). */
var UC32 = {
    ucId: "UC-32", title: "Isi Data Pribadi", actorLane: "Kandidat", maxRow: 6,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_fill", kind: "action", lane: "A", col: 0, row: 1, text: "Isi field langkah aktif, tekan lanjut" },
        { id: "d_range", kind: "decision", lane: "S", col: 0, row: 2, text: "Langkah dalam 1–8?" },
        { id: "a_e2", kind: "action", lane: "S", col: 1, row: 2, text: "Tolak (422)" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "d_email", kind: "decision", lane: "S", col: 0, row: 3, text: "Langkah 1: email belum melamar?" },
        { id: "a_e3", kind: "action", lane: "S", col: 1, row: 3, text: "Error pada field email" },
        { id: "ff_e3", kind: "flowfinal", lane: "S", col: 2, row: 3 },
        { id: "d_valid", kind: "decision", lane: "S", col: 0, row: 4, text: "Aturan langkah (rulesForStep) lolos?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 4, text: "Error per-field (422), tetap di langkah" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 4 },
        { id: "a_ok", kind: "action", lane: "S", col: 0, row: 5, text: "Kembalikan ok; langkah siap lanjut" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 6 }
    ],
    edges: [
        { from: "init", to: "a_fill" }, { from: "a_fill", to: "d_range" },
        { from: "d_range", to: "a_e2", guard: "di luar" }, { from: "a_e2", to: "ff_e2" },
        { from: "d_range", to: "d_email", guard: "1-8" },
        { from: "d_email", to: "a_e3", guard: "sudah melamar" }, { from: "a_e3", to: "ff_e3" },
        { from: "d_email", to: "d_valid", guard: "belum" },
        { from: "d_valid", to: "a_e1", guard: "gagal" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_valid", to: "a_ok", guard: "lolos" }, { from: "a_ok", to: "final_ok" }
    ]
};

/* UC-36 Terima/Tolak Penawaran (tautan tertanda). E2 link invalid/kedaluwarsa,
 * E1 sudah direspons; terima->advance, tolak->gagal; notify HR. */
var UC36 = {
    ucId: "UC-36", title: "Terima/Tolak Penawaran", actorLane: "Kandidat", maxRow: 8,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka tautan terima/tolak" },
        { id: "d_signed", kind: "decision", lane: "S", col: 0, row: 2, text: "Tautan tertanda & belum kedaluwarsa?" },
        { id: "a_e2", kind: "action", lane: "S", col: 1, row: 2, text: "Tolak akses (signed)" },
        { id: "ff_e2", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "d_responded", kind: "decision", lane: "S", col: 0, row: 3, text: "Penawaran belum direspons?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 3, text: "Tampilkan halaman sudah direspons" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 3 },
        { id: "a_choose", kind: "action", lane: "A", col: 0, row: 4, text: "Pilih terima atau tolak (isi alasan bila tolak)" },
        { id: "d_kep", kind: "decision", lane: "S", col: 0, row: 5, text: "Respons?" },
        { id: "a_accept", kind: "action", lane: "S", col: 0, row: 6, text: "Tandai Accepted, majukan (advance) ke MCU" },
        { id: "a_notify1", kind: "action", lane: "S", col: 0, row: 7, text: "Beri tahu HR Admin (PenawaranDirespon)" },
        { id: "final_accept", kind: "final", lane: "S", col: 0, row: 8 },
        { id: "a_reject", kind: "action", lane: "S", col: 1, row: 6, text: "Tandai Rejected, tahap penawaran Gagal" },
        { id: "a_notify2", kind: "action", lane: "S", col: 1, row: 7, text: "Beri tahu HR Admin (PenawaranDirespon)" },
        { id: "final_reject", kind: "final", lane: "S", col: 1, row: 8 }
    ],
    edges: [
        { from: "init", to: "a_open" }, { from: "a_open", to: "d_signed" },
        { from: "d_signed", to: "a_e2", guard: "tidak" }, { from: "a_e2", to: "ff_e2" },
        { from: "d_signed", to: "d_responded", guard: "valid" },
        { from: "d_responded", to: "a_e1", guard: "sudah direspons" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_responded", to: "a_choose", guard: "belum" },
        { from: "a_choose", to: "d_kep" },
        { from: "d_kep", to: "a_accept", guard: "terima" },
        { from: "a_accept", to: "a_notify1" }, { from: "a_notify1", to: "final_accept" },
        { from: "d_kep", to: "a_reject", guard: "tolak" },
        { from: "a_reject", to: "a_notify2" }, { from: "a_notify2", to: "final_reject" }
    ]
};

/* UC-37 Lihat Status Lamaran (tautan tertoken). E1 token tidak ditemukan (404). */
var UC37 = {
    ucId: "UC-37", title: "Lihat Status Lamaran", actorLane: "Kandidat", maxRow: 4,
    nodes: [
        { id: "init", kind: "initial", lane: "A", col: 0, row: 0 },
        { id: "a_open", kind: "action", lane: "A", col: 0, row: 1, text: "Buka tautan status lamaran" },
        { id: "d_token", kind: "decision", lane: "S", col: 0, row: 2, text: "Token ditemukan?" },
        { id: "a_e1", kind: "action", lane: "S", col: 1, row: 2, text: "Tampilkan 404" },
        { id: "ff_e1", kind: "flowfinal", lane: "S", col: 2, row: 2 },
        { id: "a_show", kind: "action", lane: "S", col: 0, row: 3, text: "Tampilkan lamaran, lowongan & tahapan beserta status" },
        { id: "final_ok", kind: "final", lane: "S", col: 0, row: 4 }
    ],
    edges: [
        { from: "init", to: "a_open" }, { from: "a_open", to: "d_token" },
        { from: "d_token", to: "a_e1", guard: "tidak" }, { from: "a_e1", to: "ff_e1" },
        { from: "d_token", to: "a_show", guard: "ya" }, { from: "a_show", to: "final_ok" }
    ]
};

/* ============================ model registry =========================== */
// Add each UC model here. main() builds every one into its own sub-package.
// DONE & verified clean: run1 UC-12/13/16/17; run2 UC-19/21/22/23/26/31/33/34/35;
// run3a UC-01..11.
// This run = linear batch 3b, final 13 (run on a FRESH empty parent package):
var MODELS = [
    UC14, UC15, UC18, UC20, UC24, UC25, UC27, UC28, UC29, UC30, UC32, UC36, UC37
];

/* ================================ main ================================ */

function main() {
    var repo = Repository;
    var sel = repo.GetTreeSelectedObject();
    if (sel === null || sel.ObjectType !== 5) {
        Session.Output("Select the TARGET PACKAGE in the Project Browser first " +
            "(single-click it), then re-run.");
        return;
    }
    var parent = sel; // otPackage == 5
    Session.Output("Parent package: " + parent.Name + "  (" + MODELS.length + " model(s))");

    var totalReal = 0;
    var m;
    for (m = 0; m < MODELS.length; m++) {
        var model = MODELS[m];
        // one sub-package per UC keeps the Project Browser tidy
        var sub = parent.Packages.AddNew(model.ucId + " " + model.title, "Package");
        sub.Update();
        parent.Packages.Refresh();
        totalReal += renderUC(repo, sub, model);
    }
    Session.Output("==== ALL DONE: " + MODELS.length + " diagram(s), " +
        totalReal + " total real issue(s) ====");
}

main();
