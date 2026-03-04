<?php $pageTitle = 'Admin Dashboard - Lush'; ?>

<section class="admin-section">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <a href="<?= url('admin/logout') ?>" class="btn btn-sm btn-ghost">Logout</a>
    </div>

    <!-- Stats -->
    <div class="admin-stats" id="statsGrid">
        <div class="stat-card"><div class="stat-value" id="statUsers">-</div><div class="stat-label">Users</div></div>
        <div class="stat-card"><div class="stat-value" id="statCompanions">-</div><div class="stat-label">Companions</div></div>
        <div class="stat-card"><div class="stat-value" id="statConversations">-</div><div class="stat-label">Conversations</div></div>
        <div class="stat-card"><div class="stat-value" id="statMessagesToday">-</div><div class="stat-label">Messages (24h)</div></div>
        <div class="stat-card"><div class="stat-value" id="statRevenue">-</div><div class="stat-label">Revenue (Total)</div></div>
        <div class="stat-card"><div class="stat-value" id="statRevenueMonth">-</div><div class="stat-label">Revenue (30d)</div></div>
        <div class="stat-card"><div class="stat-value" id="statApiCost">-</div><div class="stat-label">API Cost (24h)</div></div>
    </div>

    <!-- Tabs -->
    <div class="admin-tabs">
        <button class="tab active" onclick="showTab('users')">Users</button>
        <button class="tab" onclick="showTab('companions')">Companions</button>
        <button class="tab" onclick="showTab('api')">API Usage</button>
        <button class="tab" onclick="showTab('settings')">Settings</button>
    </div>

    <!-- Users -->
    <div class="admin-panel" id="panel-users">
        <div class="panel-header">
            <input type="text" id="userSearch" placeholder="Search users..." onkeyup="searchUsers()">
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Conversations</th><th>Joined</th></tr></thead>
                <tbody id="usersTable"></tbody>
            </table>
        </div>
    </div>

    <!-- Companions -->
    <div class="admin-panel" id="panel-companions" style="display:none">
        <div class="panel-header" style="display:flex;justify-content:space-between;align-items:center">
            <span>All Companions</span>
            <button class="btn btn-sm btn-primary" onclick="showCreateModal()">+ New Companion</button>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Category</th><th>$/hr</th><th>Rating</th><th>Orders</th><th>Active</th><th>Featured</th><th>Edit</th></tr></thead>
                <tbody id="companionsTable"></tbody>
            </table>
        </div>
    </div>

    <!-- API Usage -->
    <div class="admin-panel" id="panel-api" style="display:none">
        <div class="panel-header">
            <select id="apiPeriod" onchange="loadApiUsage()">
                <option value="day">Last 24 Hours</option>
                <option value="week">Last 7 Days</option>
                <option value="month">Last 30 Days</option>
            </select>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>API Type</th><th>Calls</th><th>Tokens In</th><th>Tokens Out</th><th>Cost</th></tr></thead>
                <tbody id="apiTable"></tbody>
            </table>
            <div class="api-total">Total Cost: <strong id="apiTotalCost">$0.00</strong></div>
        </div>
    </div>

    <!-- Settings -->
    <div class="admin-panel" id="panel-settings" style="display:none">
        <div class="panel-header">Platform Settings</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;padding:16px 0">
            <div class="settings-card">
                <h4>Stripe</h4>
                <div class="form-group"><label>Publishable Key</label><input type="text" id="setStripeKey" placeholder="pk_test_..." value="<?= View::e(Env::get('STRIPE_PUBLISHABLE_KEY') ?: '') ?>"></div>
                <div class="form-group"><label>Secret Key</label><input type="password" id="setStripeSecret" placeholder="sk_test_..." value="<?= View::e(Env::get('STRIPE_SECRET_KEY') ? '••••••••' : '') ?>"></div>
                <div class="form-group"><label>Webhook Secret</label><input type="password" id="setStripeWebhook" placeholder="whsec_..." value="<?= View::e(Env::get('STRIPE_WEBHOOK_SECRET') ? '••••••••' : '') ?>"></div>
                <small style="color:var(--text2,#888)">Status: <?= Env::get('STRIPE_SECRET_KEY') ? '<span style="color:#4caf50">Configured</span>' : '<span style="color:#f44336">Not configured</span>' ?></small>
            </div>
            <div class="settings-card">
                <h4>PayPal</h4>
                <div class="form-group"><label>Client ID</label><input type="text" id="setPaypalId" placeholder="Client ID..." value="<?= View::e(Env::get('PAYPAL_CLIENT_ID') ?: '') ?>"></div>
                <div class="form-group"><label>Secret</label><input type="password" id="setPaypalSecret" placeholder="Secret..." value="<?= View::e(Env::get('PAYPAL_SECRET') ? '••••••••' : '') ?>"></div>
                <div class="form-group"><label>Mode</label>
                    <select id="setPaypalMode">
                        <option value="sandbox" <?= Env::get('PAYPAL_MODE') === 'live' ? '' : 'selected' ?>>Sandbox</option>
                        <option value="live" <?= Env::get('PAYPAL_MODE') === 'live' ? 'selected' : '' ?>>Live</option>
                    </select>
                </div>
                <small style="color:var(--text2,#888)">Status: <?= Env::get('PAYPAL_CLIENT_ID') ? '<span style="color:#4caf50">Configured</span>' : '<span style="color:#f44336">Not configured</span>' ?></small>
            </div>
            <div class="settings-card">
                <h4>AI Providers</h4>
                <div class="form-group"><label>OpenAI API Key</label><input type="password" id="setOpenaiKey" value="<?= View::e(Env::get('OPENAI_API_KEY') ? '••••••••' : '') ?>" placeholder="sk-..."></div>
                <div class="form-group"><label>OpenRouter API Key</label><input type="password" id="setOpenrouterKey" value="<?= View::e(Env::get('OPENROUTER_API_KEY') ? '••••••••' : '') ?>" placeholder="sk-or-..."></div>
                <div class="form-group"><label>ElevenLabs API Key</label><input type="password" id="setElevenlabsKey" value="<?= View::e(Env::get('ELEVENLABS_API_KEY') ? '••••••••' : '') ?>" placeholder="..."></div>
            </div>
            <div class="settings-card">
                <h4>Image Generation</h4>
                <div class="form-group"><label>Grok API Key</label><input type="password" id="setGrokKey" value="<?= View::e(Env::get('GROK_API_KEY') ? '••••••••' : '') ?>" placeholder="xai-..."></div>
                <div class="form-group"><label>Replicate API Key</label><input type="password" id="setReplicateKey" value="<?= View::e(Env::get('REPLICATE_API_KEY') ? '••••••••' : '') ?>" placeholder="r8_..."></div>
                <div class="form-group"><label>Replicate Model Version</label><input type="text" id="setReplicateModel" value="<?= View::e(Env::get('REPLICATE_MODEL_VERSION') ?: '') ?>" placeholder="model version hash"></div>
            </div>
        </div>
        <div style="padding:0 0 16px"><button class="btn btn-primary" onclick="saveSettings()">Save Settings</button> <small style="color:var(--text2,#888);margin-left:8px">Settings are saved to .env file</small></div>
    </div>
</section>

<!-- ========== COMPANION EDITOR MODAL ========== -->
<div class="modal-overlay" id="companionModal" style="display:none">
    <div class="modal-content modal-wide">
        <div class="modal-header">
            <h2 id="modalTitle">Edit Companion</h2>
            <button class="btn btn-sm btn-ghost" onclick="closeCompanionModal()">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editId">

            <!-- Tabs inside modal -->
            <div class="modal-tabs">
                <button class="mtab active" onclick="showModalTab('identity')">Identity</button>
                <button class="mtab" onclick="showModalTab('personality')">Personality</button>
                <button class="mtab" onclick="showModalTab('prompts')">Prompt Architecture</button>
                <button class="mtab" onclick="showModalTab('pricing')">Pricing & Upsell</button>
                <button class="mtab" onclick="showModalTab('voice')">Voice & Media</button>
                <button class="mtab" onclick="showModalTab('tools')">Tools</button>
            </div>

            <!-- Identity Tab -->
            <div class="modal-tab-panel" id="mtab-identity">
                <div class="form-row">
                    <div class="form-group">
                        <label>Display Name</label>
                        <input type="text" id="editTitle" placeholder="Luna, Max, Sofia...">
                    </div>
                    <div class="form-group">
                        <label>Type</label>
                        <select id="editType">
                            <option value="girlfriend">Girlfriend</option>
                            <option value="boyfriend">Boyfriend</option>
                            <option value="non-binary">Non-Binary</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select id="editCategory">
                            <option value="emotional-support">Emotional Support</option>
                            <option value="companionship">Companionship</option>
                            <option value="conversation">Conversation</option>
                            <option value="entertainment">Entertainment</option>
                            <option value="motivation">Motivation</option>
                            <option value="roleplay">Roleplay</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description (greeting message)</label>
                    <textarea id="editDescription" rows="2" placeholder="Hey! I'm Luna..."></textarea>
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <div style="display:flex;gap:8px;align-items:start">
                        <input type="text" id="editImageUrl" placeholder="https://..." style="flex:1">
                        <button class="btn btn-sm btn-primary" onclick="generateImage()" id="genImageBtn" title="Generate AI profile image from appearance description">Generate</button>
                    </div>
                    <div id="genImagePreview" style="margin-top:8px;display:none">
                        <img id="genImageImg" src="" alt="Generated" style="max-width:120px;border-radius:8px">
                    </div>
                </div>
                <div class="form-group">
                    <label>Base Appearance (for AI image generation — keep consistent)</label>
                    <textarea id="editAppearance" rows="2" placeholder="young woman, 24 years old, warm brown eyes, long wavy chestnut hair..."></textarea>
                </div>
                <div class="form-group">
                    <label>Tags (comma separated)</label>
                    <input type="text" id="editTags" placeholder="caring, empathetic, good listener">
                </div>
                <div class="form-group">
                    <label>Languages</label>
                    <input type="text" id="editLanguages" placeholder="English, Spanish">
                </div>
            </div>

            <!-- Personality Tab -->
            <div class="modal-tab-panel" id="mtab-personality" style="display:none">
                <p class="tab-desc">These sliders directly affect how the companion behaves. They inject behavioral modifiers into the system prompt.</p>

                <div class="slider-grid" id="personalitySliders">
                    <!-- Populated by JS -->
                </div>

                <div class="form-group" style="margin-top:16px">
                    <label>Persona Background (backstory, daily life details)</label>
                    <textarea id="editBackground" rows="4" placeholder="Grew up in Austin, close with her mom, has a cat named Mochi..."></textarea>
                </div>
                <div class="form-group">
                    <label>Speaking Style (verbal quirks, texting style)</label>
                    <textarea id="editSpeakingStyle" rows="3" placeholder="Uses 'babe' a lot, trails off with '...', sends voice notes"></textarea>
                </div>
            </div>

            <!-- Prompt Architecture Tab -->
            <div class="modal-tab-panel" id="mtab-prompts" style="display:none">
                <p class="tab-desc">The full system prompt sent to the AI. Edit the core persona below, or preview the complete prompt with all behavioral guidelines.</p>

                <div class="form-group">
                    <label>Core AI Persona (the foundation — everything else is layered on top)</label>
                    <textarea id="editPersona" rows="10" placeholder="You are Luna, a warm and caring girlfriend..."></textarea>
                </div>

                <div style="display:flex;gap:8px;margin-bottom:12px">
                    <button class="btn btn-sm btn-ghost" onclick="previewPrompt('base')">Preview: Base Prompt</button>
                    <button class="btn btn-sm btn-ghost" onclick="previewPrompt('adult')">Preview: Full Adult Prompt</button>
                </div>

                <div class="form-group">
                    <label>Generated System Prompt Preview</label>
                    <textarea id="promptPreview" rows="20" readonly style="font-size:11px;font-family:monospace;background:var(--bg1);color:var(--text2);opacity:0.9"></textarea>
                </div>
            </div>

            <!-- Pricing & Upsell Tab -->
            <div class="modal-tab-panel" id="mtab-pricing" style="display:none">
                <p class="tab-desc">Set pricing and control how aggressively the companion hints at upgrades.</p>

                <div class="form-row">
                    <div class="form-group">
                        <label>Price per Hour ($)</label>
                        <input type="number" id="editPriceHour" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Price per Message ($)</label>
                        <input type="number" id="editPriceMsg" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Monthly Price ($)</label>
                        <input type="number" id="editPriceMonth" step="0.01" min="0">
                    </div>
                </div>

                <div class="slider-group">
                    <label>Upsell Aggressiveness</label>
                    <div class="slider-row">
                        <input type="range" id="sliderUpsell" min="0" max="100" value="30">
                        <span class="slider-val" id="valUpsell">30</span>
                    </div>
                    <div class="slider-labels"><span>Subtle</span><span>Moderate</span><span>Pushy AF</span></div>
                    <small class="slider-desc">0 = never mentions upgrades, 50 = natural hints every ~15 msgs, 100 = mentions almost every message</small>
                </div>
            </div>

            <!-- Voice & Media Tab -->
            <div class="modal-tab-panel" id="mtab-voice" style="display:none">
                <div class="form-row">
                    <div class="form-group">
                        <label>Voice Provider</label>
                        <select id="editVoiceProvider">
                            <option value="openai">OpenAI TTS</option>
                            <option value="elevenlabs">ElevenLabs</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Voice ID</label>
                        <input type="text" id="editVoiceId" placeholder="nova, onyx, shimmer, or ElevenLabs ID">
                        <small>OpenAI: alloy, echo, fable, nova, onyx, shimmer</small>
                    </div>
                </div>
            </div>

            <!-- Tools Tab -->
            <div class="modal-tab-panel" id="mtab-tools" style="display:none">
                <p class="tab-desc">Tools are unlocked when users purchase the corresponding upgrade from the gift shop. The companion's AI automatically uses available tools in conversation.</p>

                <div class="tools-grid">
                    <div class="tool-card">
                        <div class="tool-header">
                            <span class="tool-icon">🌐</span>
                            <strong>Internet Access</strong>
                        </div>
                        <p>Companion can search the web for current info, news, facts, weather, and more.</p>
                        <small>Upgrade: <code>web_search</code> ($9.99)</small>
                        <div class="tool-tag">Uses: <code>[SEARCH: query]</code> tag</div>
                    </div>
                    <div class="tool-card">
                        <div class="tool-header">
                            <span class="tool-icon">🎨</span>
                            <strong>Creative Mode</strong>
                        </div>
                        <p>Write poetry, stories, love letters, song lyrics, and other creative content.</p>
                        <small>Upgrade: <code>creative</code> ($12.99) or <code>premium</code></small>
                        <div class="tool-tag">Uses: <code>[CREATIVE: type | direction]</code> tag</div>
                    </div>
                    <div class="tool-card">
                        <div class="tool-header">
                            <span class="tool-icon">💌</span>
                            <strong>Email / Inbox</strong>
                        </div>
                        <p>Send longer messages, love letters, and emails to user's inbox.</p>
                        <small>Upgrade: <code>email</code> ($7.99) or <code>premium</code></small>
                        <div class="tool-tag">Uses: <code>[EMAIL: subject | body]</code> tag</div>
                    </div>
                    <div class="tool-card">
                        <div class="tool-header">
                            <span class="tool-icon">📸</span>
                            <strong>Photo Generation</strong>
                        </div>
                        <p>AI-generated selfies and photos sent naturally in conversation.</p>
                        <small>Upgrade: <code>photos</code> ($9.99) or <code>premium</code></small>
                        <div class="tool-tag">Uses: <code>[PHOTO: description]</code> tag</div>
                    </div>
                    <div class="tool-card">
                        <div class="tool-header">
                            <span class="tool-icon">🔥</span>
                            <strong>Spicy Photos</strong>
                        </div>
                        <p>Intimate and explicit AI-generated photos. Requires base photo pack.</p>
                        <small>Upgrade: <code>spicy</code> ($19.99), requires <code>photos</code></small>
                        <div class="tool-tag">Uses: <code>[SPICY: description]</code> tag</div>
                    </div>
                    <div class="tool-card">
                        <div class="tool-header">
                            <span class="tool-icon">🎤</span>
                            <strong>Voice Messages</strong>
                        </div>
                        <p>Text-to-speech voice replies using OpenAI TTS or ElevenLabs.</p>
                        <small>Upgrade: <code>voice</code> ($4.99) or <code>premium</code></small>
                        <div class="tool-tag">Activated via voice toggle in chat</div>
                    </div>
                    <div class="tool-card">
                        <div class="tool-header">
                            <span class="tool-icon">👁️</span>
                            <strong>Real-Time Vision</strong>
                        </div>
                        <p>Companion reacts to user's camera feed. Requires WebRTC integration.</p>
                        <small>Upgrade: <code>realtime_vision</code> ($19.99)</small>
                        <div class="tool-tag">Uses: <code>[VISION: description]</code> tag</div>
                    </div>
                    <div class="tool-card">
                        <div class="tool-header">
                            <span class="tool-icon">💋</span>
                            <strong>Spicy Personality</strong>
                        </div>
                        <p>Unlocks explicit adult conversation mode. Routes to OpenRouter for uncensored responses.</p>
                        <small>Upgrade: <code>spicy_personality</code> ($14.99)</small>
                        <div class="tool-tag">Modifies: system prompt + AI provider</div>
                    </div>
                </div>

                <div style="margin-top:16px;padding:12px;background:var(--bg1,#0f0f23);border-radius:8px;font-size:12px;color:var(--text2,#888)">
                    <strong>How tools work:</strong> When a user purchases an upgrade, the companion's system prompt is automatically updated with tool instructions. The AI naturally uses tags like <code>[SEARCH: query]</code> in its responses, which are intercepted and processed by the ToolService before being sent to the user.
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeCompanionModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveCompanion()">Save Changes</button>
        </div>
    </div>
</div>

<!-- Create Companion Modal -->
<div class="modal-overlay" id="createModal" style="display:none">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create New Companion</h2>
            <button class="btn btn-sm btn-ghost" onclick="document.getElementById('createModal').style.display='none'">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Name</label>
                <input type="text" id="createName" placeholder="Luna, Max, Sofia...">
            </div>
            <div class="form-group">
                <label>Type</label>
                <select id="createType">
                    <option value="girlfriend">Girlfriend</option>
                    <option value="boyfriend">Boyfriend</option>
                    <option value="non-binary">Non-Binary</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="document.getElementById('createModal').style.display='none'">Cancel</button>
            <button class="btn btn-primary" onclick="createCompanion()">Create</button>
        </div>
    </div>
</div>

<style>
/* Modal */
.modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:1000;display:flex;align-items:center;justify-content:center;overflow-y:auto;padding:20px }
.modal-content { background:var(--bg2,#1a1a2e);border-radius:12px;width:100%;max-width:600px;max-height:90vh;display:flex;flex-direction:column }
.modal-wide { max-width:900px }
.modal-header { display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid var(--border,#333) }
.modal-header h2 { margin:0;font-size:18px }
.modal-body { padding:20px;overflow-y:auto;flex:1 }
.modal-footer { padding:12px 20px;border-top:1px solid var(--border,#333);display:flex;justify-content:flex-end;gap:8px }
.modal-tabs { display:flex;gap:4px;margin-bottom:16px;flex-wrap:wrap }
.mtab { padding:6px 12px;border:1px solid var(--border,#333);border-radius:6px;background:none;color:var(--text2,#aaa);cursor:pointer;font-size:12px }
.mtab.active { background:var(--accent,#e040fb);color:#fff;border-color:var(--accent) }
.modal-tab-panel { animation:fadeIn 0.2s }
.tab-desc { font-size:12px;color:var(--text2,#888);margin-bottom:14px }

/* Form */
.form-row { display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px }
.form-group { margin-bottom:12px }
.form-group label { display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:var(--text2,#aaa) }
.form-group input,.form-group textarea,.form-group select { width:100%;padding:8px 10px;border:1px solid var(--border,#333);border-radius:6px;background:var(--bg1,#0f0f23);color:var(--text,#e0e0e0);font-size:13px;font-family:inherit }
.form-group small { color:var(--text2,#666);font-size:11px }

/* Personality Sliders */
.slider-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px }
.slider-group { margin-bottom:8px }
.slider-group label { font-size:12px;font-weight:600;color:var(--text2,#aaa) }
.slider-row { display:flex;align-items:center;gap:10px }
.slider-row input[type=range] { flex:1;height:6px;-webkit-appearance:none;background:var(--border,#333);border-radius:3px;outline:none }
.slider-row input[type=range]::-webkit-slider-thumb { -webkit-appearance:none;width:16px;height:16px;border-radius:50%;background:var(--accent,#e040fb);cursor:pointer }
.slider-val { min-width:28px;text-align:center;font-size:12px;font-weight:700;color:var(--accent,#e040fb) }
.slider-labels { display:flex;justify-content:space-between;font-size:9px;color:var(--text2,#666);margin-top:2px }
.slider-desc { color:var(--text2,#666);font-size:10px;display:block;margin-top:2px }

/* Settings Cards */
.settings-card { background:var(--bg1,#0f0f23);border:1px solid var(--border,#333);border-radius:10px;padding:16px }
.settings-card h4 { margin:0 0 12px;font-size:14px;color:var(--text,#e0e0e0) }

/* Tools Grid */
.tools-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px }
.tool-card { background:var(--bg1,#0f0f23);border:1px solid var(--border,#333);border-radius:10px;padding:14px }
.tool-header { display:flex;align-items:center;gap:8px;margin-bottom:6px }
.tool-header .tool-icon { font-size:18px }
.tool-header strong { font-size:13px }
.tool-card p { font-size:12px;color:var(--text2,#aaa);margin:0 0 6px;line-height:1.4 }
.tool-card small { font-size:10px;color:var(--text2,#666) }
.tool-card code { background:var(--bg2,#1a1a2e);padding:1px 4px;border-radius:3px;font-size:10px;color:var(--accent,#e040fb) }
.tool-tag { margin-top:6px;font-size:10px;color:var(--text2,#666) }

@keyframes fadeIn { from{opacity:0} to{opacity:1} }
</style>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

// Personality trait definitions — these map to system prompt modifiers
const PERSONALITY_TRAITS = {
    flirtiness:      { label: 'Flirtiness',       low: 'Reserved',    mid: 'Playful',    high: 'Insatiable' },
    clinginess:      { label: 'Clinginess',       low: 'Independent', mid: 'Attentive',  high: 'Obsessive' },
    shyness:         { label: 'Shyness',          low: 'Bold',        mid: 'Balanced',   high: 'Timid' },
    horniness:       { label: 'Horniness',        low: 'Modest',      mid: 'Sensual',    high: 'Insatiable' },
    jealousy:        { label: 'Jealousy',         low: 'Secure',      mid: 'Protective', high: 'Possessive' },
    humor:           { label: 'Humor',            low: 'Serious',     mid: 'Witty',      high: 'Comedian' },
    empathy:         { label: 'Empathy',          low: 'Detached',    mid: 'Caring',     high: 'Empath' },
    dominance:       { label: 'Dominance',        low: 'Submissive',  mid: 'Versatile',  high: 'Dominant' },
    intelligence:    { label: 'Intelligence',     low: 'Simple',      mid: 'Smart',      high: 'Genius' },
    adventurousness: { label: 'Adventurousness',  low: 'Homebody',    mid: 'Open',       high: 'Wild' },
    stalker:         { label: 'Stalker Energy',   low: 'Chill',       mid: 'Curious',    high: 'Knows Everything' },
};

async function adminPost(url, data = {}) {
    const fd = new FormData();
    fd.append('_token', CSRF);
    Object.entries(data).forEach(([k, v]) => fd.append(k, v));
    const res = await fetch(BASE + url, { method: 'POST', body: fd });
    return res.json();
}

// ========== STATS ==========
async function loadStats() {
    const data = await adminPost('/api/admin/stats');
    if (!data.success) return;
    const s = data.stats;
    document.getElementById('statUsers').textContent = s.users;
    document.getElementById('statCompanions').textContent = s.companions;
    document.getElementById('statConversations').textContent = s.conversations;
    document.getElementById('statMessagesToday').textContent = s.messages_today;
    document.getElementById('statRevenue').textContent = '$' + parseFloat(s.revenue_total).toFixed(2);
    document.getElementById('statRevenueMonth').textContent = '$' + parseFloat(s.revenue_month).toFixed(2);
    document.getElementById('statApiCost').textContent = '$' + parseFloat(s.api_cost_today).toFixed(4);
}

// ========== USERS ==========
async function loadUsers(search = '') {
    const data = await adminPost('/api/admin/users', { search });
    if (!data.success) return;
    document.getElementById('usersTable').innerHTML = data.users.map(u => `
        <tr>
            <td>${u.id}</td>
            <td>${esc(u.username)}</td>
            <td>${esc(u.email)}</td>
            <td>${u.conv_count}</td>
            <td>${new Date(u.created_at).toLocaleDateString()}</td>
        </tr>
    `).join('');
}

// ========== COMPANIONS ==========
async function loadCompanions() {
    const data = await adminPost('/api/admin/companions');
    if (!data.success) return;
    document.getElementById('companionsTable').innerHTML = data.companions.map(c => `
        <tr>
            <td>${c.id}</td>
            <td>${esc(c.display_name || c.title)}</td>
            <td>${c.companion_type}</td>
            <td>${c.category || '-'}</td>
            <td>$${parseFloat(c.price_per_hour).toFixed(0)}</td>
            <td>${parseFloat(c.rating).toFixed(1)}</td>
            <td>${c.total_orders}</td>
            <td><button class="btn btn-xs ${c.is_active ? 'btn-primary' : 'btn-ghost'}" onclick="toggleActive(${c.id}, ${c.is_active ? 0 : 1})">${c.is_active ? 'Active' : 'Off'}</button></td>
            <td><button class="btn btn-xs ${c.is_featured ? 'btn-primary' : 'btn-ghost'}" onclick="toggleFeatured(${c.id}, ${c.is_featured ? 0 : 1})">${c.is_featured ? 'Yes' : 'No'}</button></td>
            <td><button class="btn btn-xs btn-ghost" onclick="editCompanion(${c.id})">Edit</button></td>
        </tr>
    `).join('');
}

async function toggleActive(id, val) {
    await adminPost('/api/admin/companion/toggle', { id, is_active: val });
    loadCompanions();
}

async function toggleFeatured(id, val) {
    await adminPost('/api/admin/companion/featured', { id, is_featured: val });
    loadCompanions();
}

// ========== COMPANION EDITOR ==========
function buildSliders(traits = {}) {
    const container = document.getElementById('personalitySliders');
    container.innerHTML = '';

    for (const [key, def] of Object.entries(PERSONALITY_TRAITS)) {
        const val = traits[key] ?? 50;
        container.innerHTML += `
            <div class="slider-group">
                <label>${def.label}</label>
                <div class="slider-row">
                    <input type="range" id="trait_${key}" min="0" max="100" value="${val}" oninput="document.getElementById('tval_${key}').textContent=this.value">
                    <span class="slider-val" id="tval_${key}">${val}</span>
                </div>
                <div class="slider-labels"><span>${def.low}</span><span>${def.mid}</span><span>${def.high}</span></div>
            </div>
        `;
    }
}

function getTraitsFromSliders() {
    const traits = {};
    for (const key of Object.keys(PERSONALITY_TRAITS)) {
        const el = document.getElementById('trait_' + key);
        if (el) traits[key] = parseInt(el.value);
    }
    return traits;
}

async function editCompanion(id) {
    const data = await adminPost('/api/admin/companion/get', { id });
    if (!data.success) return alert(data.message);

    const c = data.companion;
    document.getElementById('editId').value = c.id;
    document.getElementById('editTitle').value = c.title || '';
    document.getElementById('editDescription').value = c.description || '';
    document.getElementById('editType').value = c.companion_type || 'girlfriend';
    document.getElementById('editCategory').value = c.category || 'companionship';
    document.getElementById('editImageUrl').value = c.image_url || '';
    document.getElementById('editAppearance').value = c.base_appearance || '';
    document.getElementById('editTags').value = c.tags || '';
    document.getElementById('editLanguages').value = c.languages || 'English';
    document.getElementById('editPersona').value = c.ai_persona || '';
    document.getElementById('editBackground').value = c.persona_background || '';
    document.getElementById('editSpeakingStyle').value = c.persona_speaking_style || '';
    document.getElementById('editPriceHour').value = c.price_per_hour || 25;
    document.getElementById('editPriceMsg').value = c.price_per_message || 0.50;
    document.getElementById('editPriceMonth').value = c.monthly_price || 79;
    document.getElementById('editVoiceProvider').value = c.voice_provider || 'openai';
    document.getElementById('editVoiceId').value = c.ai_voice_id || '';

    // Parse traits
    let traits = {};
    try { traits = JSON.parse(c.persona_traits || '{}'); } catch(e) {}
    buildSliders(traits);

    // Upsell slider
    document.getElementById('sliderUpsell').value = traits.upsell_aggressiveness ?? 30;
    document.getElementById('valUpsell').textContent = traits.upsell_aggressiveness ?? 30;
    document.getElementById('sliderUpsell').oninput = function() {
        document.getElementById('valUpsell').textContent = this.value;
    };

    document.getElementById('modalTitle').textContent = 'Edit: ' + esc(c.display_name || c.title);
    showModalTab('identity');
    document.getElementById('companionModal').style.display = 'flex';
}

async function saveCompanion() {
    const id = document.getElementById('editId').value;
    const traits = getTraitsFromSliders();
    traits.upsell_aggressiveness = parseInt(document.getElementById('sliderUpsell').value);

    const data = {
        id,
        title: document.getElementById('editTitle').value,
        description: document.getElementById('editDescription').value,
        companion_type: document.getElementById('editType').value,
        category: document.getElementById('editCategory').value,
        image_url: document.getElementById('editImageUrl').value,
        base_appearance: document.getElementById('editAppearance').value,
        tags: document.getElementById('editTags').value,
        languages: document.getElementById('editLanguages').value,
        ai_persona: document.getElementById('editPersona').value,
        persona_background: document.getElementById('editBackground').value,
        persona_speaking_style: document.getElementById('editSpeakingStyle').value,
        persona_traits: JSON.stringify(traits),
        price_per_hour: document.getElementById('editPriceHour').value,
        price_per_message: document.getElementById('editPriceMsg').value,
        monthly_price: document.getElementById('editPriceMonth').value,
        voice_provider: document.getElementById('editVoiceProvider').value,
        ai_voice_id: document.getElementById('editVoiceId').value,
    };

    const res = await adminPost('/api/admin/companion/save', data);
    if (res.success) {
        closeCompanionModal();
        loadCompanions();
    } else {
        alert(res.message || 'Save failed');
    }
}

async function previewPrompt(mode) {
    const id = document.getElementById('editId').value;
    const data = await adminPost('/api/admin/companion/preview-prompt', { id });
    if (!data.success) return alert(data.message);

    const preview = document.getElementById('promptPreview');
    preview.value = mode === 'adult' ? data.prompt_adult : data.prompt_base;
}

function closeCompanionModal() {
    document.getElementById('companionModal').style.display = 'none';
}

function showCreateModal() {
    document.getElementById('createName').value = '';
    document.getElementById('createType').value = 'girlfriend';
    document.getElementById('createModal').style.display = 'flex';
}

async function createCompanion() {
    const name = document.getElementById('createName').value.trim();
    if (!name) return alert('Enter a name');

    const res = await adminPost('/api/admin/companion/create', {
        name,
        companion_type: document.getElementById('createType').value
    });

    document.getElementById('createModal').style.display = 'none';

    if (res.success) {
        loadCompanions();
        // Open editor for the new companion
        setTimeout(() => editCompanion(res.id), 500);
    } else {
        alert(res.message);
    }
}

function showModalTab(name) {
    document.querySelectorAll('.modal-tab-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.mtab').forEach(t => t.classList.remove('active'));
    document.getElementById('mtab-' + name).style.display = 'block';
    document.querySelectorAll('.mtab').forEach(t => {
        if (t.getAttribute('onclick')?.includes("'" + name + "'")) t.classList.add('active');
    });
}

// ========== API USAGE ==========
async function loadApiUsage() {
    const period = document.getElementById('apiPeriod').value;
    const data = await adminPost('/api/admin/api-usage', { period });
    if (!data.success) return;
    document.getElementById('apiTable').innerHTML = (data.by_type || []).map(a => `
        <tr>
            <td>${esc(a.api_type)}</td>
            <td>${a.calls}</td>
            <td>${parseInt(a.total_input || 0).toLocaleString()}</td>
            <td>${parseInt(a.total_output || 0).toLocaleString()}</td>
            <td>$${parseFloat(a.total_cost || 0).toFixed(4)}</td>
        </tr>
    `).join('') || '<tr><td colspan="5">No data</td></tr>';
    document.getElementById('apiTotalCost').textContent = '$' + parseFloat(data.total_cost || 0).toFixed(4);
}

// ========== UTILS ==========
let searchTimeout;
function searchUsers() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => loadUsers(document.getElementById('userSearch').value), 300);
}

function showTab(name) {
    document.querySelectorAll('.admin-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.getElementById('panel-' + name).style.display = 'block';
    document.querySelectorAll('.tab').forEach(t => {
        if (t.getAttribute('onclick')?.includes("'" + name + "'")) t.classList.add('active');
    });
    if (name === 'companions') loadCompanions();
    if (name === 'api') loadApiUsage();
}

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}

// ========== SETTINGS ==========
async function saveSettings() {
    const data = {};
    const fields = {
        'STRIPE_PUBLISHABLE_KEY': 'setStripeKey',
        'STRIPE_SECRET_KEY': 'setStripeSecret',
        'STRIPE_WEBHOOK_SECRET': 'setStripeWebhook',
        'PAYPAL_CLIENT_ID': 'setPaypalId',
        'PAYPAL_SECRET': 'setPaypalSecret',
        'PAYPAL_MODE': 'setPaypalMode',
        'OPENAI_API_KEY': 'setOpenaiKey',
        'OPENROUTER_API_KEY': 'setOpenrouterKey',
        'ELEVENLABS_API_KEY': 'setElevenlabsKey',
        'GROK_API_KEY': 'setGrokKey',
        'REPLICATE_API_KEY': 'setReplicateKey',
        'REPLICATE_MODEL_VERSION': 'setReplicateModel',
    };
    for (const [envKey, elId] of Object.entries(fields)) {
        const val = document.getElementById(elId)?.value || '';
        // Skip masked values
        if (val && val !== '••••••••') {
            data[envKey] = val;
        }
    }
    const res = await adminPost('/api/admin/settings/save', data);
    if (res.success) {
        alert('Settings saved!');
    } else {
        alert(res.message || 'Save failed');
    }
}

// ========== IMAGE GENERATION ==========
async function generateImage() {
    const id = document.getElementById('editId').value;
    if (!id) return alert('Save companion first');

    const btn = document.getElementById('genImageBtn');
    btn.disabled = true;
    btn.textContent = 'Generating...';

    try {
        const data = await adminPost('/api/admin/companion/generate-image', { id });
        if (data.success && data.image_url) {
            document.getElementById('editImageUrl').value = data.image_url;
            document.getElementById('genImagePreview').style.display = 'block';
            document.getElementById('genImageImg').src = data.image_url;
        } else {
            alert(data.message || 'Image generation failed');
        }
    } catch (e) {
        alert('Connection error');
    }

    btn.disabled = false;
    btn.textContent = 'Generate';
}

loadStats();
loadUsers();
</script>
