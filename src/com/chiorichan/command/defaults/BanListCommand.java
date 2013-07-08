package com.chiorichan.command.defaults;

import java.util.ArrayList;
import java.util.List;

import org.apache.commons.lang.Validate;
import com.chiorichan.ChioriFramework;
import com.chiorichan.OfflinePlayer;
import com.chiorichan.command.CommandSender;
import com.chiorichan.util.StringUtil;

import com.google.common.collect.ImmutableList;

public class BanListCommand extends VanillaCommand {
    private static final List<String> BANLIST_TYPES = ImmutableList.of("ips", "players");

    public BanListCommand() {
        super("banlist");
        this.description = "View all players banned from this server";
        this.usageMessage = "/banlist [ips|players]";
        this.setPermission("bukkit.command.ban.list");
    }

    @Override
    public boolean execute(CommandSender sender, String currentAlias, String[] args) {
        if (!testPermission(sender)) return true;

        // TODO: ips support
        StringBuilder message = new StringBuilder();
        OfflinePlayer[] banlist = ChioriFramework.getServer().getBannedPlayers().toArray(new OfflinePlayer[0]);

        for (int x = 0; x < banlist.length; x++) {
            if (x != 0) {
                if (x == banlist.length - 1) {
                    message.append(" and ");
                } else {
                    message.append(", ");
                }
            }
            message.append(banlist[x].getName());
        }

        sender.sendMessage("There are " + banlist.length + " total banned players:");
        sender.sendMessage(message.toString());
        return true;
    }

    @Override
    public List<String> tabComplete(CommandSender sender, String alias, String[] args) {
        Validate.notNull(sender, "Sender cannot be null");
        Validate.notNull(args, "Arguments cannot be null");
        Validate.notNull(alias, "Alias cannot be null");

        if (args.length == 1) {
            return StringUtil.copyPartialMatches(args[0], BANLIST_TYPES, new ArrayList<String>(BANLIST_TYPES.size()));
        }
        return ImmutableList.of();
    }
}