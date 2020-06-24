; <?php /*

[blocks/group/embed]

label = "Blocks: Group"
icon = ellipsis-h

wildcard[label] = "Match block IDs (section-name-* or comma-separated list)"
wildcard[type] = text
wildcard[initial] = "section-name-*"
wildcard[regex] = "/(\*|,)/"
wildcard[message] = "Please include an asterisk (*) in your pattern, or a list of IDs"

units[label] = "How many columns?"
units[type] = select
units[initial] = "50,50"
units[require] = "apps/blocks/lib/Functions.php"
units[callback] = "blocks_units"

level[label] = "Title heading level"
level[type] = select
level[require] = "apps/blocks/lib/Functions.php"
level[callback] = "blocks_heading_levels"
level[initial] = "h3"

; */ ?>
