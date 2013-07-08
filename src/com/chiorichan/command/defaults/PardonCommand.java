package com.chiorichan.command.defaults;

import java.util.ArrayList;
import java.util.List;

import org.apache.commons.lang.Validate;
import com.chiorichan.ChioriFramework;
import com.chiorichan.ChatColor;
import com.chiorichan.OfflinePlayer;
import com.chiorichan.command.Command;
import com.chiorichan.command.CommandSender;
import com.chiorichan.util.StringUtil;

import com.google.common.collect.ImmutableList;

public class PardonCommand extends VanillaCommand {
    public PardonCommand() {
        super("pardon");
        this.description = "Allows the specified player to use this server";
        this.usageMessage = "/pardon <player>";
        this.setPermission("bukkit.command.unban.player");
    }

    @Override
    public boolean execute(CommandSender sender, String currentAlias, String[] args) {
        if (!testPermission(sender)) return true;
        if (args.length != 1)  {
            sender.sendMessage(ChatColor.RED + "Usage: " + usageMessage);
            return false;
        }

        ChioriFramework.getOfflinePlayer(args[0]).setBanned(false);
        Command.broadcastCommandMessage(sender, "Pardoned " + args[0]);
        return true;
    }

    @Override
    public List<String> tabComplete(CommandSender sender, String alias, String[] args) throws IllegalArgumentException {
        Validate.notNull(sender, "Sender cannot be null");
        Validate.notNull(args, "Arguments cannot be null");
        Validate.notNull(alias, "Alias cannot be null");

        if (args.length == 1) {
            List<String> completions = new ArrayList<String>();
            for (OfflinePlayer player : ChioriFramework.getBannedPlayers()) {
                String name = player.getName();
                if (StringUtil.startsWithIgnoreCase(name, args[0])) {
                    completions.add(name);
                }
            }
            return completions;
        }
        return ImmutableList.of();
    }
}